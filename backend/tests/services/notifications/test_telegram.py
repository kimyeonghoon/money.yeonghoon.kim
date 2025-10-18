"""
TelegramNotificationService 테스트

Telegram 봇을 통한 알림 전송 기능을 테스트합니다.
"""
import pytest
from unittest.mock import Mock, patch, AsyncMock
import httpx


class TestTelegramNotificationService:
    """TelegramNotificationService 테스트 클래스"""

    def test_telegram_service_inherits_base(self):
        """TelegramNotificationService는 BaseNotificationService를 상속받아야 함"""
        # Given & When: TelegramNotificationService 임포트
        from app.services.notifications.telegram import TelegramNotificationService
        from app.services.notifications.base import BaseNotificationService

        # Then: BaseNotificationService의 서브클래스여야 함
        assert issubclass(TelegramNotificationService, BaseNotificationService)

    def test_telegram_service_can_be_instantiated(self):
        """TelegramNotificationService는 인스턴스화 가능해야 함"""
        # Given: TelegramNotificationService
        from app.services.notifications.telegram import TelegramNotificationService

        # When: 인스턴스 생성
        service = TelegramNotificationService()

        # Then: 인스턴스가 생성되어야 함
        assert service is not None

    @patch('app.services.notifications.telegram.config')
    def test_is_enabled_returns_true_when_enabled(self, mock_config):
        """TELEGRAM_ENABLED=true일 때 is_enabled()는 True 반환"""
        # Given: Telegram이 활성화된 설정
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = True
        service = TelegramNotificationService()

        # When: is_enabled() 호출
        result = service.is_enabled()

        # Then: True 반환
        assert result is True

    @patch('app.services.notifications.telegram.config')
    def test_is_enabled_returns_false_when_disabled(self, mock_config):
        """TELEGRAM_ENABLED=false일 때 is_enabled()는 False 반환"""
        # Given: Telegram이 비활성화된 설정
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = False
        service = TelegramNotificationService()

        # When: is_enabled() 호출
        result = service.is_enabled()

        # Then: False 반환
        assert result is False

    @pytest.mark.asyncio
    @patch('app.services.notifications.telegram.config')
    @patch('app.services.notifications.telegram.httpx.AsyncClient')
    async def test_send_login_alert_success(self, mock_client_class, mock_config):
        """로그인 알림 전송 성공"""
        # Given: Telegram이 활성화되고 API 응답이 성공
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = True
        mock_config.TELEGRAM_BOT_TOKEN = "test_bot_token"
        mock_config.TELEGRAM_CHAT_ID = "test_chat_id"

        mock_response = Mock()
        mock_response.status_code = 200
        mock_response.json.return_value = {"ok": True}

        mock_client = AsyncMock()
        mock_client.__aenter__.return_value = mock_client
        mock_client.__aexit__.return_value = None
        mock_client.post = AsyncMock(return_value=mock_response)
        mock_client_class.return_value = mock_client

        service = TelegramNotificationService()

        # When: 로그인 알림 전송
        result = await service.send_login_alert(
            user_email="test@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0 Test Browser"
        )

        # Then: 성공 응답 및 API 호출 확인
        assert result is True
        mock_client.post.assert_called_once()
        call_args = mock_client.post.call_args
        assert "sendMessage" in call_args[0][0]
        assert call_args[1]["json"]["chat_id"] == "test_chat_id"
        assert "test@example.com" in call_args[1]["json"]["text"]
        assert "192.168.1.1" in call_args[1]["json"]["text"]

    @pytest.mark.asyncio
    @patch('app.services.notifications.telegram.config')
    @patch('app.services.notifications.telegram.httpx.AsyncClient')
    async def test_send_login_alert_api_failure(self, mock_client_class, mock_config):
        """Telegram API 실패 시 False 반환"""
        # Given: API가 실패 응답 반환
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = True
        mock_config.TELEGRAM_BOT_TOKEN = "test_bot_token"
        mock_config.TELEGRAM_CHAT_ID = "test_chat_id"

        mock_response = Mock()
        mock_response.status_code = 400
        mock_response.json.return_value = {"ok": False}

        mock_client = AsyncMock()
        mock_client.__aenter__.return_value = mock_client
        mock_client.__aexit__.return_value = None
        mock_client.post = AsyncMock(return_value=mock_response)
        mock_client_class.return_value = mock_client

        service = TelegramNotificationService()

        # When: 로그인 알림 전송
        result = await service.send_login_alert(
            user_email="test@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0"
        )

        # Then: False 반환
        assert result is False

    @pytest.mark.asyncio
    @patch('app.services.notifications.telegram.config')
    @patch('app.services.notifications.telegram.httpx.AsyncClient')
    async def test_send_login_alert_network_error(self, mock_client_class, mock_config):
        """네트워크 오류 시 False 반환"""
        # Given: 네트워크 오류 발생
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = True
        mock_config.TELEGRAM_BOT_TOKEN = "test_bot_token"
        mock_config.TELEGRAM_CHAT_ID = "test_chat_id"

        mock_client = AsyncMock()
        mock_client.__aenter__.return_value = mock_client
        mock_client.__aexit__.return_value = None
        mock_client.post = AsyncMock(side_effect=httpx.RequestError("Network error"))
        mock_client_class.return_value = mock_client

        service = TelegramNotificationService()

        # When: 로그인 알림 전송
        result = await service.send_login_alert(
            user_email="test@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0"
        )

        # Then: False 반환 (예외를 catch하여 처리)
        assert result is False

    @pytest.mark.asyncio
    @patch('app.services.notifications.telegram.config')
    async def test_send_login_alert_when_disabled(self, mock_config):
        """비활성화 상태에서는 알림 전송하지 않음"""
        # Given: Telegram이 비활성화된 상태
        from app.services.notifications.telegram import TelegramNotificationService

        mock_config.TELEGRAM_ENABLED = False

        service = TelegramNotificationService()

        # When: 로그인 알림 전송 시도
        result = await service.send_login_alert(
            user_email="test@example.com",
            ip_address="192.168.1.1",
            user_agent="Mozilla/5.0"
        )

        # Then: False 반환 (알림 전송 안 함)
        assert result is False
