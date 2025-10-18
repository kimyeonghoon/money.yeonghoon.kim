"""
구조화된 로깅 시스템

JSON 형식의 로그를 출력하여 모니터링 도구(Datadog, ELK 등)에서 쉽게 파싱할 수 있습니다.
"""

import logging
import sys
import json
from datetime import datetime
from typing import Any, Dict


class JSONFormatter(logging.Formatter):
    """JSON 포맷 로거

    모든 로그를 JSON 형식으로 출력합니다.
    """

    def format(self, record: logging.LogRecord) -> str:
        """로그 레코드를 JSON 문자열로 변환

        Args:
            record: 로그 레코드

        Returns:
            str: JSON 형식의 로그 문자열
        """
        log_data: Dict[str, Any] = {
            "timestamp": datetime.utcnow().isoformat(),
            "level": record.levelname,
            "logger": record.name,
            "message": record.getMessage(),
            "module": record.module,
            "function": record.funcName,
            "line": record.lineno,
        }

        if record.exc_info:
            log_data["exception"] = self.formatException(record.exc_info)

        if hasattr(record, "user_id"):
            log_data["user_id"] = record.user_id

        if hasattr(record, "request_id"):
            log_data["request_id"] = record.request_id

        return json.dumps(log_data, ensure_ascii=False)


def setup_logging(level: str = "INFO") -> None:
    """로깅 시스템 초기화

    Args:
        level: 로그 레벨 (DEBUG, INFO, WARNING, ERROR, CRITICAL)

    Example:
        >>> from app.core.logging import setup_logging
        >>> setup_logging("INFO")
        >>> import logging
        >>> logger = logging.getLogger(__name__)
        >>> logger.info("Application started")
    """
    root_logger = logging.getLogger()
    root_logger.setLevel(level)

    handler = logging.StreamHandler(sys.stdout)
    handler.setFormatter(JSONFormatter())

    root_logger.addHandler(handler)

    uvicorn_logger = logging.getLogger("uvicorn")
    uvicorn_logger.handlers = []
    uvicorn_logger.addHandler(handler)

    uvicorn_access = logging.getLogger("uvicorn.access")
    uvicorn_access.handlers = []
    uvicorn_access.addHandler(handler)


def get_logger(name: str) -> logging.Logger:
    """로거 인스턴스 가져오기

    Args:
        name: 로거 이름 (보통 __name__ 사용)

    Returns:
        logging.Logger: 로거 인스턴스

    Example:
        >>> logger = get_logger(__name__)
        >>> logger.info("User logged in", extra={"user_id": 123})
    """
    return logging.getLogger(name)
