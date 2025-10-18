# 개발 헬퍼 스크립트

이 디렉토리에는 개발을 편리하게 하기 위한 스크립트들이 있습니다.

## 💻 Windows 사용자

Windows에서는 다음 중 하나를 사용하세요:

1. **Git Bash** (권장)
   - Git for Windows 설치 시 함께 설치됨
   - 프로젝트 폴더에서 우클릭 → "Git Bash Here"

2. **WSL (Windows Subsystem for Linux)**
   - Windows 10/11에서 Linux 환경 사용
   - `wsl` 명령어로 진입

3. **PowerShell 대안**
   - 각 스크립트의 명령어를 직접 실행
   - 예: `docker-compose -f docker-compose.dev.yml up -d`

## 🚀 빠른 시작

### 한 번에 개발 환경 시작

**Linux / macOS / Git Bash:**
```bash
bash scripts/dev-start.sh
```

또는 IP 주소와 함께:
```bash
bash scripts/dev-start.sh 192.168.0.100
```

**Windows PowerShell:**
```powershell
.\scripts\dev-start.ps1
.\scripts\dev-start.ps1 192.168.0.100
```

**기능:**
- ✅ 백엔드 API 서버 시작
- ✅ Metro 서버 시작
- ✅ adb WiFi 연결 (선택)
- ✅ 앱 빌드 및 설치 (선택)

## 📱 WiFi 디버깅

### adb 연결

```bash
bash scripts/adb-connect.sh [IP주소]
```

**예시:**
```bash
# IP 주소를 인자로 전달
bash scripts/adb-connect.sh 192.168.0.100

# 또는 대화형으로 입력
bash scripts/adb-connect.sh
```

### 문제 해결

```bash
bash scripts/adb-troubleshoot.sh
```

**자동으로 수행:**
- ✅ Metro 서버 상태 확인
- ✅ adb 서버 재시작
- ✅ 연결된 기기 확인
- ✅ 네트워크 연결 테스트

## 📋 스크립트 목록

### Bash 스크립트 (Linux/macOS/Git Bash)

| 스크립트 | 설명 | 사용법 |
|---------|------|--------|
| `dev-start.sh` | 전체 개발 환경 시작 | `bash scripts/dev-start.sh [IP]` |
| `adb-connect.sh` | WiFi adb 연결 | `bash scripts/adb-connect.sh [IP]` |
| `adb-troubleshoot.sh` | 연결 문제 해결 | `bash scripts/adb-troubleshoot.sh` |
| `backup-mysql.sh` | MySQL 데이터베이스 백업 | `bash scripts/backup-mysql.sh [환경]` |
| `restore-mysql.sh` | MySQL 데이터베이스 복원 | `bash scripts/restore-mysql.sh <백업파일> [환경]` |
| `check-secrets.sh` | 민감 정보 검사 | `bash scripts/check-secrets.sh` |

### PowerShell 스크립트 (Windows)

| 스크립트 | 설명 | 사용법 |
|---------|------|--------|
| `dev-start.ps1` | 전체 개발 환경 시작 | `.\scripts\dev-start.ps1 [IP]` |

## 🔐 실행 권한 (Linux/macOS)

처음 사용 시 실행 권한을 부여해야 합니다:

```bash
chmod +x scripts/*.sh
```

또는 개별적으로:
```bash
chmod +x scripts/dev-start.sh
chmod +x scripts/adb-connect.sh
chmod +x scripts/adb-troubleshoot.sh
chmod +x scripts/backup-mysql.sh
chmod +x scripts/restore-mysql.sh
chmod +x scripts/check-secrets.sh
```

## 🔧 수동 명령어

스크립트를 사용하지 않고 수동으로 실행하려면:

### 1. 서버 시작
```bash
docker-compose -f docker-compose.dev.yml up -d
```

### 2. adb 연결
```bash
docker-compose -f docker-compose.dev.yml exec frontend adb connect 192.168.0.100:5555
```

### 3. 기기 확인
```bash
docker-compose -f docker-compose.dev.yml exec frontend adb devices
```

### 4. 앱 빌드 및 설치
```bash
docker-compose -f docker-compose.dev.yml exec frontend npm run android
```

## 💡 팁

### 자주 사용하는 IP 저장

`.env` 파일에 추가:
```env
ANDROID_DEVICE_IP=192.168.0.100
```

스크립트에서 사용:
```bash
source .env
bash scripts/adb-connect.sh $ANDROID_DEVICE_IP
```

### 별칭(alias) 만들기

`.bashrc` 또는 `.zshrc`에 추가:
```bash
alias dev-start='bash scripts/dev-start.sh'
alias adb-connect='bash scripts/adb-connect.sh'
alias adb-fix='bash scripts/adb-troubleshoot.sh'
```

그럼 이렇게 사용:
```bash
dev-start
adb-connect 192.168.0.100
adb-fix
```

## 🗄️ 데이터베이스 백업

### MySQL 백업

Docker 컨테이너에서 실행 중인 MySQL 데이터베이스를 단발적으로 백업합니다.

```bash
# 개발 환경 백업
bash scripts/backup-mysql.sh

# 또는 명시적으로
bash scripts/backup-mysql.sh dev

# 프로덕션 환경 백업
bash scripts/backup-mysql.sh prod
```

**백업 파일 위치:**
```
backups/mysql_dev_20250117_143000.sql
backups/mysql_prod_20250117_143000.sql
```

**자동 정리:**
- 30일 이상 된 백업 파일은 자동으로 삭제됩니다

### MySQL 복원

백업 파일로부터 데이터베이스를 복원합니다.

```bash
# 개발 환경 복원
bash scripts/restore-mysql.sh backups/mysql_dev_20250117_143000.sql

# 프로덕션 환경 복원
bash scripts/restore-mysql.sh backups/mysql_prod_20250117_143000.sql prod
```

**⚠️  주의사항:**
- 복원 시 기존 데이터가 모두 삭제됩니다!
- 반드시 현재 데이터를 백업한 후 복원하세요
- 복원 전 확인 프롬프트가 표시됩니다

**백업 파일 목록:**
```bash
# 사용 가능한 백업 파일 목록 확인
ls -lht backups/mysql_*.sql
```

## 🔐 보안 검사

### 민감 정보 자동 검사

```bash
bash scripts/check-secrets.sh
```

**검사 항목:**
- ✅ .env 파일
- ✅ 하드코딩된 비밀번호
- ✅ API 키
- ✅ Private Key
- ✅ 인증 토큰

**커밋 전 자동 검사 (pre-commit hook):**
```bash
# Git hooks 설정
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit
```

이후 `git commit` 시 자동으로 민감 정보 검사!

## 📖 추가 문서

- [SECURITY.md](../docs/SECURITY.md) - 보안 가이드 (민감 정보 관리)
- [WIFI_DEBUGGING.md](../docs/WIFI_DEBUGGING.md) - WiFi 디버깅 완전 가이드
- [DEVELOPMENT.md](../docs/DEVELOPMENT.md) - 개발 환경 가이드
- [WHY_DOCKER.md](../docs/WHY_DOCKER.md) - 왜 Docker인가?

## 🐛 문제 발생 시

1. **스크립트 실행 권한 오류**
   ```bash
   chmod +x scripts/*.sh
   ```

2. **adb 연결 실패**
   ```bash
   bash scripts/adb-troubleshoot.sh
   ```

3. **Docker 컨테이너가 실행 안 됨**
   ```bash
   docker-compose -f docker-compose.dev.yml ps
   docker-compose -f docker-compose.dev.yml logs
   ```

4. **앱 빌드 실패**
   ```bash
   # Gradle 캐시 정리
   docker-compose -f docker-compose.dev.yml exec frontend bash
   cd android && ./gradlew clean
   ```

## 🎓 스크립트 커스터마이징

필요에 따라 스크립트를 수정하세요:

1. 스크립트 복사
2. 프로젝트에 맞게 수정
3. `scripts/` 디렉토리에 저장
4. 실행 권한 부여: `chmod +x scripts/your-script.sh`

예시: 여러 기기에 동시 배포
```bash
#!/bin/bash
# scripts/deploy-all.sh

DEVICES=("192.168.0.100:5555" "192.168.0.101:5555")

for device in "${DEVICES[@]}"; do
    echo "배포 중: $device"
    docker-compose -f docker-compose.dev.yml exec frontend adb connect $device
    docker-compose -f docker-compose.dev.yml exec frontend adb -s $device install app-debug.apk
done
```

---

**Happy Coding! 🚀**
