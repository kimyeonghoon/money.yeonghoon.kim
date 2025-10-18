# Notification Services 📢

플러그인 방식의 확장 가능한 알림 시스템입니다.

## 현재 지원하는 알림 채널

- ✅ **Telegram** - 로그인 알림

## 빠른 시작

### 1. Telegram 알림 활성화

#### Step 1: Telegram 봇 생성
1. Telegram에서 [@BotFather](https://t.me/botfather) 검색
2. `/newbot` 명령어로 봇 생성
3. 봇 토큰 복사 (예: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`)

#### Step 2: Chat ID 확인
1. 봇과 대화 시작 (아무 메시지나 전송)
2. 브라우저에서 다음 URL 접속:
   ```
   https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
   ```
3. `"chat":{"id":123456789}` 부분에서 ID 복사

#### Step 3: 환경 변수 설정
`.env` 파일에 추가:
```env
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=123456789
```

#### Step 4: 완료!
로그인 시 자동으로 Telegram 알림이 전송됩니다.

### 2. 알림 비활성화

```env
TELEGRAM_ENABLED=false
```

설정만 변경하면 코드 수정 없이 알림을 끄고 켤 수 있습니다!

---

## 새로운 알림 채널 추가하기

템플릿 사용자가 Email, Slack, Discord 등 새 채널을 추가하는 방법입니다.

### 예시: Email 알림 추가

#### Step 1: 테스트 작성 (TDD)

`backend/tests/services/notifications/test_email.py`:
```python
import pytest
from app.services.notifications.email import EmailNotificationService

class TestEmailNotificationService:
    def test_email_service_inherits_base(self):
        from app.services.notifications.base import BaseNotificationService
        assert issubclass(EmailNotificationService, BaseNotificationService)

    @pytest.mark.asyncio
    async def test_send_login_alert_success(self):
        service = EmailNotificationService()
        result = await service.send_login_alert(
            user_email="test@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0"
        )
        assert result is True
```

#### Step 2: 구현

`backend/app/services/notifications/email.py`:
```python
from app.services.notifications.base import BaseNotificationService
from app.config import settings as config
import smtplib
from email.mime.text import MIMEText

class EmailNotificationService(BaseNotificationService):
    def is_enabled(self) -> bool:
        return config.EMAIL_ENABLED

    async def send_login_alert(
        self, user_email: str, ip_address: str, user_agent: str
    ) -> bool:
        if not self.is_enabled():
            return False

        try:
            msg = MIMEText(
                f"로그인 알림\n\n"
                f"사용자: {user_email}\n"
                f"IP: {ip_address}\n"
                f"디바이스: {user_agent}"
            )
            msg['Subject'] = '로그인 알림'
            msg['From'] = config.EMAIL_FROM
            msg['To'] = config.EMAIL_TO

            with smtplib.SMTP(config.SMTP_HOST, config.SMTP_PORT) as server:
                server.starttls()
                server.login(config.SMTP_USER, config.SMTP_PASSWORD)
                server.send_message(msg)

            return True
        except Exception as e:
            logger.error(f"Email send failed: {e}")
            return False
```

#### Step 3: 설정 추가

`backend/app/config.py`에 추가:
```python
EMAIL_ENABLED: bool = False
EMAIL_FROM: str = ""
EMAIL_TO: str = ""
SMTP_HOST: str = "smtp.gmail.com"
SMTP_PORT: int = 587
SMTP_USER: str = ""
SMTP_PASSWORD: str = ""
```

#### Step 4: 로그인 API에 통합

`backend/app/api/v1/auth.py`:
```python
from app.services.notifications.email import EmailNotificationService

# login 함수 내부에 추가
email_notifier = EmailNotificationService()
if email_notifier.is_enabled():
    await email_notifier.send_login_alert(
        user_email=user.email,
        ip_address=ip_address,
        user_agent=user_agent
    )
```

#### Step 5: 테스트 실행
```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/services/notifications/test_email.py -v
```

---

## 아키텍처 설계

### Strategy Pattern

```
BaseNotificationService (추상 클래스)
    ↑
    ├── TelegramNotificationService
    ├── EmailNotificationService
    ├── SlackNotificationService
    └── DiscordNotificationService
```

### 핵심 원칙

1. **플러그인 방식**: 새 채널 추가 시 기존 코드 수정 최소화
2. **환경 변수 제어**: 코드 변경 없이 on/off 가능
3. **실패 허용**: 알림 실패가 비즈니스 로직을 방해하지 않음
4. **TDD**: 테스트 먼저, 구현은 나중

### 파일 구조

```
app/services/notifications/
├── __init__.py              # 모듈 초기화
├── base.py                  # 추상 베이스 클래스
├── telegram.py              # Telegram 구현
├── email.py                 # Email 구현 (예시)
└── README.md                # 이 파일
```

---

## API 사용 예시

### Python에서 직접 호출

```python
from app.services.notifications.telegram import TelegramNotificationService

notifier = TelegramNotificationService()

if notifier.is_enabled():
    success = await notifier.send_login_alert(
        user_email="user@example.com",
        ip_address="192.168.1.1",
        user_agent="Mozilla/5.0 Chrome/91.0"
    )

    if success:
        print("알림 전송 성공!")
```

### 여러 채널 동시 사용

```python
from app.services.notifications.telegram import TelegramNotificationService
from app.services.notifications.email import EmailNotificationService

notifiers = [
    TelegramNotificationService(),
    EmailNotificationService(),
]

for notifier in notifiers:
    if notifier.is_enabled():
        await notifier.send_login_alert(user_email, ip, user_agent)
```

---

## 테스트

### 전체 알림 테스트 실행
```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/services/notifications/ -v
```

### 특정 채널만 테스트
```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/services/notifications/test_telegram.py -v
```

### 커버리지 확인
```bash
docker-compose -f docker-compose.dev.yml exec backend pytest tests/services/notifications/ --cov=app/services/notifications --cov-report=html
```

---

## 보안 고려사항

### ⚠️ 절대 커밋하지 말 것
- `.env` 파일
- Telegram 봇 토큰
- SMTP 비밀번호
- API 키

### ✅ 권장사항
- 환경 변수로 모든 민감 정보 관리
- `.gitignore`에 `.env` 추가 확인
- 프로덕션 환경에서는 시크릿 관리 서비스 사용 (AWS Secrets Manager, HashiCorp Vault 등)

---

## FAQ

**Q: 알림 전송이 실패하면 로그인도 실패하나요?**
A: 아니요. 알림 실패는 로그인을 방해하지 않습니다. 로그만 기록됩니다.

**Q: 여러 채널에 동시에 알림을 보낼 수 있나요?**
A: 네! 각 서비스를 순차적으로 호출하면 됩니다.

**Q: 새 알림 유형(로그아웃, 비밀번호 변경 등)을 추가하려면?**
A: `BaseNotificationService`에 새 추상 메서드를 추가하고, 모든 구현 클래스에서 해당 메서드를 구현하면 됩니다.

**Q: 비동기가 아닌 동기 방식으로 사용할 수 있나요?**
A: 현재는 async/await를 사용합니다. 동기 방식이 필요하면 별도의 베이스 클래스를 만드는 것을 권장합니다.

---

## 기여하기

새로운 알림 채널을 추가하셨나요? PR을 보내주세요!

1. 테스트 작성 (TDD)
2. 구현
3. 문서 업데이트 (이 README의 "지원하는 알림 채널" 섹션)
4. PR 제출

---

## 라이선스

MIT License - 자유롭게 사용하세요!
