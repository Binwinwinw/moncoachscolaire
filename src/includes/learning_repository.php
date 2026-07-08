<?php

class LearningRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getActiveProfileForUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM student_profiles WHERE user_id = ? AND status = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$userId, 'active']);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public function getActivePathForProfile(int $profileId, string $objective): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT Id AS id, Title AS title, Subject AS subject, Level AS level, Description AS description, Duration AS duration, Difficulty AS difficulty, IsActive AS is_active, Id AS path_id, profile_id, objective, current_stage, target_competency, estimated_duration, status, created_at, updated_at FROM learning_paths WHERE profile_id = ? AND objective = ? AND status = ? ORDER BY Id DESC LIMIT 1'
        );
        $stmt->execute([$profileId, $objective, 'active']);
        $path = $stmt->fetch(PDO::FETCH_ASSOC);

        return $path ?: null;
    }

    public function getContentUnits(string $schoolLevel, string $subject): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM content_units WHERE school_level = ? AND subject = ? AND is_active = 1 ORDER BY difficulty, id DESC'
        );
        $stmt->execute([$schoolLevel, $subject]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContentUnit(int $contentUnitId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM content_units WHERE id = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$contentUnitId]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        return $unit ?: null;
    }

    public function getRecentProgressEvents(int $profileId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM progress_events WHERE profile_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $profileId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProfileById(int $profileId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM student_profiles WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$profileId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return $profile ?: null;
    }

    public function getActiveRecommendation(int $profileId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM recommendations WHERE profile_id = ? AND status = ? AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY score DESC, id DESC LIMIT 1'
        );
        $stmt->execute([$profileId, 'pending']);
        $recommendation = $stmt->fetch(PDO::FETCH_ASSOC);

        return $recommendation ?: null;
    }

    public function getSuggestedExerciseForContentUnit(array $contentUnit): ?array
    {
        if (!function_exists('getExercisesByLevel')) {
            require_once __DIR__ . '/exercice_loader.php';
        }

        $subject = trim((string) ($contentUnit['subject'] ?? ''));
        $schoolLevel = trim((string) ($contentUnit['school_level'] ?? ''));
        if ($subject === '' || $schoolLevel === '') {
            return null;
        }

        $levelCandidates = [$schoolLevel];
        $normalizedLevel = strtolower($schoolLevel);
        $normalizedMap = [
            '6e' => '6eme',
            '6eme' => '6eme',
            '5e' => '5eme',
            '5eme' => '5eme',
            '4e' => '4eme',
            '4eme' => '4eme',
            '3e' => '3eme',
            '3eme' => '3eme',
            '2nde' => 'Seconde',
            'seconde' => 'Seconde',
            '1ere' => 'Premiere',
            'premiere' => 'Premiere',
            'terminale' => 'Terminale',
            'bac' => 'Terminale',
        ];

        if (isset($normalizedMap[$normalizedLevel])) {
            $levelCandidates[] = $normalizedMap[$normalizedLevel];
        }

        if (str_contains($schoolLevel, 'ème')) {
            $levelCandidates[] = str_replace('ème', 'eme', $schoolLevel);
        }

        $levelCandidates = array_values(array_unique(array_filter($levelCandidates)));

        foreach ($levelCandidates as $levelCandidate) {
            $exercises = getExercisesByLevel($levelCandidate, $subject, 1, 0);
            if (!empty($exercises)) {
                return $exercises[0];
            }
        }

        return null;
    }

    private function collectSubjectCandidates(?array $profile, ?array $contentUnit = null): array
    {
        $candidates = [];

        if ($profile) {
            $prioritySubjects = $profile['priority_subjects'] ?? null;
            if (is_string($prioritySubjects)) {
                $decoded = json_decode($prioritySubjects, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (is_string($item)) {
                            $candidates[] = trim($item);
                        } elseif (is_array($item) && isset($item['subject'])) {
                            $candidates[] = trim((string) $item['subject']);
                        }
                    }
                }
            }

            if (($profile['preferred_subject'] ?? null) !== null) {
                $candidates[] = trim((string) $profile['preferred_subject']);
            }
        }

        if ($contentUnit) {
            $candidates[] = trim((string) ($contentUnit['subject'] ?? ''));
        }

        $candidates = array_values(array_unique(array_filter($candidates, static function ($value): bool {
            return trim((string) $value) !== '';
        })));

        return $candidates;
    }

    private function findFallbackContentUnit(?array $profile, string $objective = 'consolidation'): ?array
    {
        if (!class_exists('LearningModel')) {
            require_once __DIR__ . '/learning_model.php';
        }

        $schoolLevel = '';
        if ($profile) {
            $schoolLevel = LearningModel::normalizeSchoolLevel((string) ($profile['school_level'] ?? '')) ?? '';
        }

        $subjectCandidates = $this->collectSubjectCandidates($profile);
        if ($subjectCandidates === []) {
            $subjectCandidates[] = 'Mathématiques';
        }

        $queries = [];
        if ($schoolLevel !== '') {
            foreach ($subjectCandidates as $subject) {
                $queries[] = [$schoolLevel, $subject];
            }
            $queries[] = [$schoolLevel, ''];
        } else {
            foreach ($subjectCandidates as $subject) {
                $queries[] = ['', $subject];
            }
            $queries[] = ['', ''];
        }

        foreach ($queries as $query) {
            [$level, $subject] = $query;
            $stmt = $this->pdo->prepare(
                'SELECT * FROM content_units WHERE (? = "" OR school_level = ?) AND (? = "" OR subject = ?) AND is_active = 1 ORDER BY difficulty, id DESC LIMIT 1'
            );
            $stmt->execute([$level, $level, $subject, $subject]);
            $unit = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($unit) {
                return $unit;
            }
        }

        return null;
    }

    public function buildRecommendationContext(int $profileId, string $objective = 'consolidation'): array
    {
        $profile = $this->getProfileById($profileId);
        $recommendation = $this->getActiveRecommendation($profileId);
        $contentUnit = null;
        $exercise = null;

        if ($recommendation && !empty($recommendation['content_unit_id'])) {
            $contentUnit = $this->getContentUnit((int) $recommendation['content_unit_id']);
        }

        if (!$contentUnit) {
            $contentUnit = $this->findFallbackContentUnit($profile, $objective);
        }

        if ($contentUnit) {
            $exercise = $this->getSuggestedExerciseForContentUnit($contentUnit);
        }

        $copy = $this->buildNextStepCopy($contentUnit ?: [], $exercise);

        return [
            'profile' => $profile,
            'recommendation' => $recommendation,
            'content_unit' => $contentUnit,
            'exercise' => $exercise,
            'next_step' => $copy,
        ];
    }

    public function buildNextStepCopy(array $contentUnit, ?array $exercise = null): array
    {
        $subject = trim((string) ($contentUnit['subject'] ?? ''));
        $difficulty = strtolower(trim((string) ($contentUnit['difficulty'] ?? '')));
        $exerciseTitle = trim((string) ($exercise['Title'] ?? ''));

        $label = 'Commencer la révision';
        $title = 'Une activité à faire maintenant';
        $description = 'Une petite étape simple pour reprendre le fil du parcours.';

        if ($subject !== '') {
            if ($exerciseTitle !== '') {
                $title = 'Reprendre l’exercice de ' . $subject;
                $label = 'Reprendre l’exercice';
                $description = 'Tu peux reprendre cette activité pour renforcer la notion de ' . $subject . '.';
            } elseif (in_array($difficulty, ['facile', 'easy', 'débutant', 'beginner'], true)) {
                $title = 'Commencer la révision de ' . $subject;
                $label = 'Commencer la révision';
                $description = 'Une mise en route douce pour consolider la notion de ' . $subject . '.';
            } else {
                $title = 'Consolider ' . $subject;
                $label = 'Revoir la notion';
                $description = 'Un passage utile pour renforcer ' . $subject . ' avec un exercice adapté.';
            }
        }

        return [
            'label' => $label,
            'title' => $title,
            'description' => $description,
        ];
    }

    public function getStudentDashboard(int $profileId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT lp.Id AS id, lp.Title AS title, lp.objective, lp.current_stage, lp.target_competency, lp.estimated_duration, COUNT(pe.id) AS events_count FROM learning_paths lp LEFT JOIN progress_events pe ON pe.path_id = lp.Id WHERE lp.profile_id = ? GROUP BY lp.Id, lp.Title, lp.objective, lp.current_stage, lp.target_competency, lp.estimated_duration ORDER BY lp.Id DESC'
        );
        $stmt->execute([$profileId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
