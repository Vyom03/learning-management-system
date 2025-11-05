# Git Setup Instructions

## Initial Setup

1. **Initialize Git Repository** (if not already done)
```bash
git init
```

2. **Add Remote Repository**
```bash
git remote add origin https://github.com/Vyom03/learning-management-system.git
```

3. **Stage All Files**
```bash
git add .
```

4. **Commit Changes**
```bash
git commit -m "Initial commit: Complete LMS with Vue.js frontend and Laravel backend"
```

5. **Push to GitHub**
```bash
git branch -M main
git push -u origin main
```

## If Repository Already Has Content

If the remote repository has files (like README), you may need to pull first:

```bash
git pull origin main --allow-unrelated-histories
```

Then resolve any conflicts and push:
```bash
git push -u origin main
```

## Future Updates

```bash
git add .
git commit -m "Description of changes"
git push
```


