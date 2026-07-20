# Mobile assets - La Voix de Dieu Tabernacle de Kindu

Dépose ici :

- `logo.png` — 1024x1024 idéalement, version complète
- `logo.svg` — déjà fourni (placeholder, utilisé par `flutter_svg`)
- `icon.png` — 1024x1024 pour l'icône d'app (Android/iOS)
- `splash.png` — image de splash screen

Pour générer les icônes Android/iOS à partir de `icon.png` :
```bash
flutter pub add flutter_launcher_icons --dev
# config dans pubspec.yaml puis :
dart run flutter_launcher_icons
```

Le `pubspec.yaml` est déjà configuré pour inclure le dossier `assets/`.
