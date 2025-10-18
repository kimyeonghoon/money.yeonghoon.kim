"""
공통 Pydantic 스키마

여러 API에서 재사용 가능한 공통 스키마를 정의합니다.
"""

from typing import Optional, Dict, Any
from pydantic import BaseModel, Field


class ErrorResponse(BaseModel):
    """표준화된 에러 응답 스키마

    모든 API 에러는 이 형식으로 반환됩니다.

    Attributes:
        error: 에러 코드 (예: "EMAIL_ALREADY_EXISTS")
        message: 사용자 친화적 에러 메시지
        details: 추가 에러 정보 (선택 사항)

    Example:
        {
            "error": "EMAIL_ALREADY_EXISTS",
            "message": "이 이메일은 이미 등록되어 있습니다",
            "details": {"email": "test@example.com"}
        }
    """

    error: str = Field(..., description="에러 코드")
    message: str = Field(..., description="에러 메시지")
    details: Optional[Dict[str, Any]] = Field(None, description="추가 에러 정보")


class PaginationParams(BaseModel):
    """페이지네이션 파라미터

    리스트 조회 API에서 사용하는 페이지네이션 설정입니다.

    Attributes:
        skip: 건너뛸 항목 수 (오프셋)
        limit: 가져올 최대 항목 수

    Example:
        # 첫 20개 항목
        skip=0, limit=20

        # 다음 20개 항목
        skip=20, limit=20

    Note:
        - skip은 0 이상
        - limit은 1~100 사이 (기본값: 20)
    """

    skip: int = Field(0, ge=0, description="건너뛸 항목 수")
    limit: int = Field(20, ge=1, le=100, description="가져올 최대 항목 수")


class MessageResponse(BaseModel):
    """간단한 메시지 응답

    성공 메시지나 간단한 정보를 반환할 때 사용합니다.

    Attributes:
        message: 메시지 내용

    Example:
        {"message": "사용자가 성공적으로 삭제되었습니다"}
    """

    message: str = Field(..., description="메시지 내용")
