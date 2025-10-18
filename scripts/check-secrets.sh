#!/bin/bash

# 민감 정보 검사 스크립트
# 프로젝트 전체에서 민감 정보를 검색합니다.

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔍 프로젝트 민감 정보 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 색상
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

# 검사할 디렉토리
SEARCH_DIRS="backend frontend scripts docs"

# 제외할 디렉토리
EXCLUDE_DIRS="node_modules|__pycache__|.git|.venv|venv|htmlcov|.pytest_cache|.expo"

# 검사 카운터
FOUND_COUNT=0

echo -e "${BLUE}검사 중: $SEARCH_DIRS${NC}"
echo ""

# 1. .env 파일 검사
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  환경 변수 파일 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

ENV_FILES=$(find . -type f -name ".env*" ! -name ".env.example" 2>/dev/null | grep -vE "$EXCLUDE_DIRS")

if [ -n "$ENV_FILES" ]; then
    echo -e "${RED}❌ .env 파일 발견:${NC}"
    echo "$ENV_FILES" | while read file; do
        echo -e "   ${YELLOW}$file${NC}"
        ((FOUND_COUNT++))
    done
    echo ""
    echo -e "${YELLOW}⚠️  .env 파일은 git에 커밋하지 마세요!${NC}"
    echo "   .env.example만 커밋하고, .env는 .gitignore에 추가하세요."
else
    echo -e "${GREEN}✅ .env 파일 없음 (안전)${NC}"
fi

echo ""

# 2. 하드코딩된 비밀번호 검사
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  하드코딩된 비밀번호 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PASSWORD_PATTERNS=(
    'password\s*=\s*["\047][^"\047]{8,}["\047]'
    'PASSWORD\s*=\s*["\047][^"\047]{8,}["\047]'
    'pwd\s*=\s*["\047][^"\047]{8,}["\047]'
    'passwd\s*=\s*["\047][^"\047]{8,}["\047]'
)

for pattern in "${PASSWORD_PATTERNS[@]}"; do
    RESULTS=$(grep -rniE "$pattern" $SEARCH_DIRS 2>/dev/null | grep -vE "$EXCLUDE_DIRS|.example|# Example|# 예시|test_")
    if [ -n "$RESULTS" ]; then
        echo -e "${RED}❌ 의심스러운 패턴 발견: $pattern${NC}"
        echo "$RESULTS" | head -5
        echo ""
        ((FOUND_COUNT++))
    fi
done

if [ $FOUND_COUNT -eq 0 ]; then
    echo -e "${GREEN}✅ 하드코딩된 비밀번호 없음${NC}"
fi

echo ""

# 3. API 키 검사
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  API 키 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

API_KEY_PATTERNS=(
    'AKIA[0-9A-Z]{16}'  # AWS Access Key
    'api[_-]?key["\047]?\s*[:=]\s*["\047][^"\047]{20,}["\047]'
    'apikey\s*=\s*["\047][^"\047]{20,}["\047]'
)

API_FOUND=false
for pattern in "${API_KEY_PATTERNS[@]}"; do
    RESULTS=$(grep -rniE "$pattern" $SEARCH_DIRS 2>/dev/null | grep -vE "$EXCLUDE_DIRS|.example|# Example|# 예시")
    if [ -n "$RESULTS" ]; then
        echo -e "${RED}❌ API 키 패턴 발견:${NC}"
        echo "$RESULTS" | head -5
        echo ""
        API_FOUND=true
        ((FOUND_COUNT++))
    fi
done

if [ "$API_FOUND" = false ]; then
    echo -e "${GREEN}✅ API 키 없음${NC}"
fi

echo ""

# 4. Private Key 검사
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  Private Key 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PRIVATE_KEY_PATTERNS=(
    "-----BEGIN RSA PRIVATE KEY-----"
    "-----BEGIN PRIVATE KEY-----"
    "-----BEGIN OPENSSH PRIVATE KEY-----"
)

KEY_FOUND=false
for pattern in "${PRIVATE_KEY_PATTERNS[@]}"; do
    RESULTS=$(grep -rl "$pattern" $SEARCH_DIRS 2>/dev/null | grep -vE "$EXCLUDE_DIRS")
    if [ -n "$RESULTS" ]; then
        echo -e "${RED}❌ Private Key 발견:${NC}"
        echo "$RESULTS"
        echo ""
        KEY_FOUND=true
        ((FOUND_COUNT++))
    fi
done

if [ "$KEY_FOUND" = false ]; then
    echo -e "${GREEN}✅ Private Key 없음${NC}"
fi

echo ""

# 5. 인증 토큰 검사
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5️⃣  인증 토큰 검사"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

TOKEN_PATTERNS=(
    'bearer\s+[a-zA-Z0-9\-._~+/]+=*'
    'token\s*=\s*["\047][^"\047]{30,}["\047]'
)

TOKEN_FOUND=false
for pattern in "${TOKEN_PATTERNS[@]}"; do
    RESULTS=$(grep -rniE "$pattern" $SEARCH_DIRS 2>/dev/null | grep -vE "$EXCLUDE_DIRS|.example|# Example|# 예시|test_")
    if [ -n "$RESULTS" ]; then
        echo -e "${RED}❌ 토큰 패턴 발견:${NC}"
        echo "$RESULTS" | head -5
        echo ""
        TOKEN_FOUND=true
        ((FOUND_COUNT++))
    fi
done

if [ "$TOKEN_FOUND" = false ]; then
    echo -e "${GREEN}✅ 하드코딩된 토큰 없음${NC}"
fi

echo ""

# 최종 결과
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 검사 완료"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $FOUND_COUNT -gt 0 ]; then
    echo -e "${RED}⚠️  $FOUND_COUNT 개의 문제 발견!${NC}"
    echo ""
    echo "해결 방법:"
    echo "1. 민감 정보를 환경 변수로 이동 (.env 파일)"
    echo "2. .env 파일을 .gitignore에 추가"
    echo "3. 코드에서는 os.getenv() 또는 config 사용"
    echo ""
    echo "이미 커밋된 민감 정보는:"
    echo "  git filter-branch 또는 BFG Repo-Cleaner로 제거"
    echo "  자세한 내용: docs/SECURITY.md"
    echo ""
    exit 1
else
    echo -e "${GREEN}✅ 민감 정보 없음 - 안전합니다!${NC}"
    echo ""
    echo "정기적으로 이 스크립트를 실행하세요:"
    echo "  bash scripts/check-secrets.sh"
    echo ""
    exit 0
fi
