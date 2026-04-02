<?php
/**
 * Script pour créer les tables Courses manuellement
 */

require_once __DIR__ . '/src/database/connection.php';

echo "🔄 Création des tables Courses...\n\n";

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur: Pas de connexion PDO\n");
}

// Table Courses
echo "1. Création table Courses...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `Courses` (
            `Id` INT AUTO_INCREMENT PRIMARY KEY,
            `Subject` VARCHAR(60) NOT NULL,
            `Level` VARCHAR(10) NOT NULL,
            `CourseNumber` INT DEFAULT 1,
            `Title` VARCHAR(250) NOT NULL,
            `Description` LONGTEXT,
            `FilePath` VARCHAR(500),
            `Keywords` LONGTEXT,
            `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `IX_Courses_Subject_Level` (`Subject`, `Level`),
            INDEX `IX_Courses_FilePath` (`FilePath`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Table Courses créée\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Table ExerciseCourseLinks
echo "\n2. Création table ExerciseCourseLinks...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `ExerciseCourseLinks` (
            `Id` INT AUTO_INCREMENT PRIMARY KEY,
            `ExerciseId` INT NOT NULL,
            `CourseId` INT NOT NULL,
            `LinkType` VARCHAR(20) DEFAULT 'theory',
            `LinkedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `UQ_ExerciseCourseLinks` (`ExerciseId`, `CourseId`, `LinkType`),
            INDEX `IX_ExerciseCourseLinks_ExerciseId` (`ExerciseId`),
            INDEX `IX_ExerciseCourseLinks_CourseId` (`CourseId`),
            CONSTRAINT `FK_ExerciseCourseLinks_Exercises` 
                FOREIGN KEY (`ExerciseId`) REFERENCES `Exercises`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_ExerciseCourseLinks_Courses` 
                FOREIGN KEY (`CourseId`) REFERENCES `Courses`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Table ExerciseCourseLinks créée\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Table UserCourseProgress
echo "\n3. Création table UserCourseProgress...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `UserCourseProgress` (
            `Id` INT AUTO_INCREMENT PRIMARY KEY,
            `UserId` INT NOT NULL,
            `CourseId` INT NOT NULL,
            `StartedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `CompletedAt` DATETIME,
            `TimeSpent` INT DEFAULT 0,
            `Status` VARCHAR(20) DEFAULT 'started',
            `Notes` LONGTEXT,
            `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `UQ_UserCourseProgress` (`UserId`, `CourseId`),
            INDEX `IX_UserCourseProgress_UserId_Status` (`UserId`, `Status`),
            CONSTRAINT `FK_UserCourseProgress_Users` 
                FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_UserCourseProgress_Courses` 
                FOREIGN KEY (`CourseId`) REFERENCES `Courses`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Table UserCourseProgress créée\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Table CourseViewEvents
echo "\n4. Création table CourseViewEvents...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `CourseViewEvents` (
            `Id` INT AUTO_INCREMENT PRIMARY KEY,
            `UserId` INT NOT NULL,
            `CourseId` INT NOT NULL,
            `ViewedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `DurationSeconds` INT,
            `ScrollDepth` INT,
            `Source` VARCHAR(100),
            INDEX `IX_CourseViewEvents_CourseId_ViewedAt` (`CourseId`, `ViewedAt`),
            CONSTRAINT `FK_CourseViewEvents_Users` 
                FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_CourseViewEvents_Courses` 
                FOREIGN KEY (`CourseId`) REFERENCES `Courses`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Table CourseViewEvents créée\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Table CourseComments
echo "\n5. Création table CourseComments...\n";
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `CourseComments` (
            `Id` INT AUTO_INCREMENT PRIMARY KEY,
            `UserId` INT NOT NULL,
            `CourseId` INT NOT NULL,
            `Comment` LONGTEXT,
            `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `Status` VARCHAR(20) DEFAULT 'pending',
            INDEX `IX_CourseComments_CourseId` (`CourseId`),
            INDEX `IX_CourseComments_UserId` (`UserId`),
            CONSTRAINT `FK_CourseComments_Users` 
                FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `FK_CourseComments_Courses` 
                FOREIGN KEY (`CourseId`) REFERENCES `Courses`(`Id`) 
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "   ✅ Table CourseComments créée\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n✅ Toutes les tables ont été créées avec succès!\n";

// Maintenant ajoutons quelques cours d'exemple pour Mathématiques 6ème
echo "\n📚 Insertion de cours d'exemple pour Mathématiques 6ème...\n";

$coursesData = [
    [
        'subject' => 'Mathématiques',
        'level' => '6ème',
        'number' => 1,
        'title' => 'Les nombres entiers',
        'desc' => 'Découvrez les nombres entiers, leur lecture, leur écriture et comment les comparer.',
        'keywords' => 'nombres,entiers,unités,dizaines,centaines,milliers'
    ],
    [
        'subject' => 'Mathématiques',
        'level' => '6ème',
        'number' => 2,
        'title' => 'Les fractions simples',
        'desc' => 'Introduction aux fractions : comprendre ce qu\'est une fraction et comment la représenter.',
        'keywords' => 'fractions,numérateur,dénominateur,parts,demi,tiers,quart'
    ],
    [
        'subject' => 'Mathématiques',
        'level' => '6ème',
        'number' => 3,
        'title' => 'Les figures géométriques',
        'desc' => 'Reconnaissance et propriétés des figures géométriques de base : cercle, triangle, carré, rectangle.',
        'keywords' => 'géométrie,figures,cercle,triangle,carré,rectangle,côtés,angles'
    ]
];

try {
    $stmt = $pdo->prepare("
        INSERT INTO Courses (Subject, Level, CourseNumber, Title, Description, Keywords)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($coursesData as $course) {
        $stmt->execute([
            $course['subject'],
            $course['level'],
            $course['number'],
            $course['title'],
            $course['desc'],
            $course['keywords']
        ]);
        echo "   ✅ Cours créé: " . $course['title'] . "\n";
    }
    
    echo "\n✅ Cours d'exemple insérés avec succès!\n";
} catch (PDOException $e) {
    echo "\n❌ Erreur insertion cours: " . $e->getMessage() . "\n";
}

echo "\n🎉 Installation terminée! Rechargez la page des cours pour voir les résultats.\n";
