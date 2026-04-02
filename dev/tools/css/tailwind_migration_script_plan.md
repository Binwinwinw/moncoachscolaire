# Plan de script d’automatisation migration Tailwind (pages secondaires)

## Objectif
Automatiser la migration des pages secondaires PHP vers Tailwind, en remplaçant les classes CSS legacy par des classes Tailwind, tout en conservant les hooks JS et la structure HTML.

## Étapes du script (Node.js ou PHP)
1. **Lister les fichiers cibles**
   - Scanner `src/pages/` pour tous les `.php` hors pages critiques déjà migrées.
2. **Analyser chaque fichier**
   - Détecter les balises principales (`body`, `main`, `section`, `h1`, `p`, `button`, etc.).
   - Remplacer les classes CSS legacy par des classes Tailwind prédéfinies (mapping).
   - Ajouter les classes utilitaires Tailwind sur les balises clés si manquantes.
   - Conserver les classes/id utilisés par le JS (hooks).
3. **Sauvegarder un backup de chaque fichier avant modification**
4. **Appliquer les modifications**
   - Écrire le fichier modifié.
5. **Loguer les changements**
   - Générer un rapport `.md` listant les fichiers modifiés et les hooks conservés.
6. **Test rapide**
   - Option : lancer un script Playwright ou curl pour vérifier que chaque page renvoie bien un code 200.

## Mapping de classes (exemple)
- `body.legal-page` → `bg-gray-50 text-gray-900`
- `.container` → `max-w-3xl mx-auto px-4 py-12`
- `.card` → `bg-white shadow-xl rounded-2xl p-8 md:p-12 mb-8 border border-gray-100`
- `h1` → `text-3xl md:text-4xl font-bold text-center mb-8`
- `p` → `text-lg text-gray-700 mb-4`

## Sécurité
- Ne jamais supprimer une classe/id utilisée dans un script JS.
- Toujours faire un backup avant modification.

## Rollback
- Restaurer le backup en cas de souci.

---

> Pour adapter à d’autres types de pages, dupliquer ce plan et ajuster le mapping.
