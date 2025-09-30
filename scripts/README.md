# 자동화 스크립트

## 월간 스냅샷 자동 생성

### create-monthly-snapshot.sh

매월 자산 데이터의 스냅샷을 자동으로 생성하는 스크립트입니다.

#### 사용법

```bash
# 자동으로 이전 달의 스냅샷 생성
./create-monthly-snapshot.sh

# 특정 년/월의 스냅샷 생성
./create-monthly-snapshot.sh 2025 9
```

#### Cron 설정

```bash
# 매월 1일 00:01에 이전 달의 스냅샷 자동 생성
1 0 1 * * /home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/create-monthly-snapshot.sh
```

**참고**:
- 매월 말일 23:59가 아닌 **다음 달 1일 00:01**에 실행하는 것이 안전합니다
- 말일이 28일, 30일, 31일로 다르기 때문에 cron으로 처리가 복잡함
- 스크립트는 자동으로 이전 달을 계산하여 스냅샷 생성

#### 로그 확인

```bash
# 스냅샷 생성 로그
tail -f /home/kim-yeonghoon/workspace/money.yeonghoon.kim/logs/snapshot-202509.log

# Cron 실행 로그
tail -f /home/kim-yeonghoon/workspace/money.yeonghoon.kim/logs/cron.log
```

#### 인증 설정

스크립트는 환경변수를 통해 로그인 정보를 받습니다:
- `SNAPSHOT_EMAIL`: 로그인 이메일
- `SNAPSHOT_PASSWORD`: 로그인 비밀번호

**Cron 환경변수 설정**:
```bash
# crontab -e
SNAPSHOT_EMAIL=your@email.com
SNAPSHOT_PASSWORD=yourpassword

1 0 1 * * /home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/create-monthly-snapshot.sh >> /home/kim-yeonghoon/workspace/money.yeonghoon.kim/logs/cron.log 2>&1
```

**수동 실행 시**:
```bash
SNAPSHOT_EMAIL=your@email.com SNAPSHOT_PASSWORD=yourpass \
  /home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/create-monthly-snapshot.sh
```

#### 수동 실행 예제

```bash
# 오늘 저녁 9월 스냅샷 생성
/home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/create-monthly-snapshot.sh 2025 9

# 내일 자동으로 10월 스냅샷 생성될 것임 (cron)
```

## 보안 주의사항

⚠️ **중요**:
- `.snapshot_cookies` 파일에는 인증 정보가 포함되어 있습니다
- 파일 권한을 600으로 설정하여 소유자만 읽을 수 있도록 합니다
- Git에는 커밋하지 않습니다 (.gitignore에 추가됨)

```bash
chmod 600 /home/kim-yeonghoon/workspace/money.yeonghoon.kim/scripts/.snapshot_cookies
```