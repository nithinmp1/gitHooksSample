# 🚀 Git File Version Checker

This script ensures that you are always working with the **latest version** of a file when committing changes. If a **newer version exists in another branch**, the commit will be blocked to prevent version conflicts.

## 📌 Features
- ✅ **Automatically checks file versions before commit**
- ✅ **Reads the latest versions from `config.php` in all branches**
- ✅ **Blocks commits of outdated files**
- ✅ **Allows commits directly to `master` without checks**

---

## 📂 Setup & Installation

### 1️⃣ **Clone the Repository**
```sh
# Clone your repo (if not already cloned)
git clone <your-repository-url>
cd <your-repository>
```

### 2️⃣ **Add the Pre-Commit Hook**
This ensures the version check runs automatically before every commit.

```sh
echo '#!/bin/bash
php check_file_versions.php' > .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

> **Note:** Ensure `check_file_versions.php` exists in the root directory.

---

## 🏃‍♂️ Running the Script Manually
You can manually check file versions before committing:

```sh
php check_file_versions.php
```

If any file is outdated, you will see an error message like:
```
❌ Error: You are editing an outdated version of 'example-1.0.php'. Latest version is 2.0.
```

---

## 📌 How It Works
1. **Checks the current branch**
   - If on `master`, the script **skips the check** ✅
2. **Gets all branches sorted by creation date**
3. **Reads `config.php` in each branch** to find the latest version of each file
4. **Compares your file version with the latest version**
   - If your file is **outdated**, the commit is blocked ❌
   - If your file is **up-to-date**, the commit proceeds ✅

---

## 📌 Example File Naming Convention
The script expects filenames in the following format:
```
filename-<version>.php
```
✅ Example:
```
user-auth-1.0.php
payment-gateway-2.5.php
```

---

## ❓ Troubleshooting
### ❌ `Error: You are editing an outdated version of '<file>.php'`
🔹 **Solution:**
- Check out the **latest version** of the file from another branch.
- Rename your file to match the latest version.

### ❌ `Error: Could not retrieve branches`
🔹 **Solution:**
- Ensure you have the latest repository changes:
```sh
git fetch --all
```

### ❌ `Config.php file missing in a branch`
🔹 **Solution:**
- Ensure that `config.php` exists in **each branch**.

---

## 🛠️ Contributing
Feel free to contribute by submitting pull requests or reporting issues!

---

## 📜 License
This project is open-source and available under the [MIT License](LICENSE).

