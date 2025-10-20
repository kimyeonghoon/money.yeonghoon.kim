"""
Telegram Notification Service

Telegram 봇 API를 사용하여 로그인 알림을 전송합니다.

설정 방법:
    1. BotFather에서 봇 생성 후 토큰 발급
    2. 채팅방 ID 확인 (getUpdates API 사용)
    3. .env 파일에 설정:
        TELEGRAM_ENABLED=true
        TELEGRAM_BOT_TOKEN=your_bot_token
        TELEGRAM_CHAT_ID=your_chat_id

Example:
    service = TelegramNotificationService()
    if service.is_enabled():
        await service.send_login_alert(
            user_email="user@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0..."
        )
"""

import httpx
import logging
from datetime import datetime
from app.services.notifications.base import BaseNotificationService
from app.config import settings as config
from app.core.constants import TELEGRAM_API_TIMEOUT, MAX_USER_AGENT_LENGTH

logger = logging.getLogger(__name__)


class TelegramNotificationService(BaseNotificationService):
    """Telegram 알림 서비스

    Telegram 봇 API를 통해 로그인 알림을 전송합니다.

    Attributes:
        bot_token (str): Telegram 봇 토큰 (환경 변수에서 로드)
        chat_id (str): 메시지를 전송할 채팅방 ID
        api_base_url (str): Telegram Bot API 기본 URL

    Note:
        - 비활성화 상태에서는 알림을 전송하지 않음
        - API 오류 시 False 반환 (로그인 자체는 성공 처리)
    """

    def __init__(self):
        """Telegram 서비스 초기화

        환경 변수에서 봇 토큰과 채팅 ID를 로드합니다.
        """
        self.bot_token = config.TELEGRAM_BOT_TOKEN
        self.chat_id = config.TELEGRAM_CHAT_ID
        self.api_base_url = f"https://api.telegram.org/bot{self.bot_token}"

    def is_enabled(self) -> bool:
        """Telegram 알림 활성화 여부 확인

        환경 변수 TELEGRAM_ENABLED에 따라 결정됩니다.

        Returns:
            bool: TELEGRAM_ENABLED=true일 때 True, 아니면 False

        Example:
            >>> service = TelegramNotificationService()
            >>> if service.is_enabled():
            ...     await service.send_login_alert(...)
        """
        return config.TELEGRAM_ENABLED

    async def send_verification_code(self, user_email: str, code: str) -> bool:
        """인증 코드를 Telegram으로 전송

        Args:
            user_email (str): 사용자 이메일
            code (str): 6자리 인증 코드

        Returns:
            bool: 전송 성공 시 True, 실패 또는 비활성화 시 False

        Example:
            >>> service = TelegramNotificationService()
            >>> success = await service.send_verification_code(
            ...     "user@example.com",
            ...     "123456"
            ... )

        Note:
            - 비활성화 상태에서는 False 반환
            - API 오류 시 예외를 catch하여 False 반환
        """
        if not self.is_enabled():
            logger.debug("Telegram notifications are disabled")
            return False

        try:
            message = self._format_verification_message(user_email, code)
            return await self._send_message(message)
        except Exception as e:
            logger.error(f"Failed to send Telegram verification code: {e}")
            return False

    async def send_login_alert(
        self, user_email: str, ip_address: str, user_agent: str
    ) -> bool:
        """로그인 알림을 Telegram으로 전송

        Args:
            user_email (str): 로그인한 사용자 이메일
            ip_address (str): 로그인 IP 주소
            user_agent (str): 브라우저/디바이스 정보

        Returns:
            bool: 전송 성공 시 True, 실패 또는 비활성화 시 False

        Example:
            >>> service = TelegramNotificationService()
            >>> success = await service.send_login_alert(
            ...     "user@example.com",
            ...     "192.168.1.1",
            ...     "Mozilla/5.0 Chrome..."
            ... )

        Note:
            - 비활성화 상태에서는 False 반환 (알림 전송하지 않음)
            - API 오류 시 예외를 catch하여 False 반환
            - 로그인 자체는 알림 실패와 무관하게 성공 처리
        """
        if not self.is_enabled():
            logger.debug("Telegram notifications are disabled")
            return False

        try:
            message = self._format_login_message(user_email, ip_address, user_agent)
            return await self._send_message(message)
        except Exception as e:
            logger.error(f"Failed to send Telegram login alert: {e}")
            return False

    def _format_verification_message(self, user_email: str, code: str) -> str:
        """인증 코드 메시지 포맷팅

        Args:
            user_email (str): 사용자 이메일
            code (str): 인증 코드

        Returns:
            str: 포맷팅된 메시지 (Markdown 형식)

        Example:
            🔐 2단계 인증

            👤 사용자: user@example.com
            🔢 인증 코드: 123456

            ⏰ 유효 시간: 5분
            ⚠️ 이 코드를 타인과 공유하지 마세요!
        """
        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        return (
            "🔐 *2단계 인증*\n\n"
            f"👤 사용자: `{user_email}`\n"
            f"🔢 인증 코드: `{code}`\n\n"
            f"⏰ 유효 시간: *5분*\n"
            f"⚠️ 이 코드를 타인과 공유하지 마세요!\n"
            f"🕐 요청 시각: `{now}`"
        )

    def _format_login_message(
        self, user_email: str, ip_address: str, user_agent: str
    ) -> str:
        """로그인 알림 메시지 포맷팅

        Args:
            user_email (str): 사용자 이메일
            ip_address (str): IP 주소
            user_agent (str): 브라우저/디바이스 정보

        Returns:
            str: 포맷팅된 메시지 (Markdown 형식)

        Example:
            🔐 로그인 알림

            👤 사용자: user@example.com
            📍 IP 주소: 192.168.1.1
            🖥 디바이스: Mozilla/5.0...
            🕐 시각: 2025-01-17 14:30:25
        """
        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        return (
            "🔐 *로그인 알림*\n\n"
            f"👤 사용자: `{user_email}`\n"
            f"📍 IP 주소: `{ip_address}`\n"
            f"🖥 디바이스: `{user_agent[:MAX_USER_AGENT_LENGTH]}...`\n"
            f"🕐 시각: `{now}`"
        )

    async def _send_message(self, text: str) -> bool:
        """Telegram API를 통해 메시지 전송

        Args:
            text (str): 전송할 메시지 내용

        Returns:
            bool: 전송 성공 시 True, 실패 시 False

        Raises:
            httpx.RequestError: 네트워크 오류 발생 시

        Note:
            - parse_mode=Markdown으로 포맷팅 지원
            - 타임아웃: 10초
        """
        url = f"{self.api_base_url}/sendMessage"
        payload = {
            "chat_id": self.chat_id,
            "text": text,
            "parse_mode": "Markdown",
        }

        try:
            async with httpx.AsyncClient(timeout=TELEGRAM_API_TIMEOUT) as client:
                response = await client.post(url, json=payload)

                if response.status_code == 200:
                    result = response.json()
                    if result.get("ok"):
                        logger.info(f"Telegram message sent successfully to chat {self.chat_id}")
                        return True
                    else:
                        logger.error(f"Telegram API returned error: {result}")
                        return False
                else:
                    logger.error(
                        f"Telegram API request failed with status {response.status_code}: {response.text}"
                    )
                    return False

        except httpx.RequestError as e:
            logger.error(f"Network error while sending Telegram message: {e}")
            raise
