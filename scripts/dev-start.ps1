# PowerShell용 개발 환경 시작 스크립트
# 사용법: .\scripts\dev-start.ps1 [IP주소]

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "🚀 개발 환경 시작" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host ""

# 1. 백엔드 및 Metro 서버 시작
Write-Host "1️⃣  백엔드 및 Metro 서버 시작 중..." -ForegroundColor Yellow
Write-Host ""

docker-compose -f docker-compose.dev.yml up -d

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ 서버 시작 완료" -ForegroundColor Green
    Write-Host ""
    Write-Host "   - 백엔드 API: http://localhost:8000"
    Write-Host "   - API 문서: http://localhost:8000/docs"
    Write-Host "   - Metro 서버: http://localhost:8081"
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "❌ 서버 시작 실패" -ForegroundColor Red
    exit 1
}

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

# 2. 서버가 완전히 시작될 때까지 대기
Write-Host ""
Write-Host "2️⃣  서버 초기화 대기 중..." -ForegroundColor Yellow
Start-Sleep -Seconds 3
Write-Host "✅ 준비 완료" -ForegroundColor Green
Write-Host ""

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

# 3. WiFi 디버깅 연결 (선택)
Write-Host ""
Write-Host "3️⃣  WiFi 디버깅 연결" -ForegroundColor Yellow
Write-Host ""

$deviceIp = $args[0]
if (-not $deviceIp) {
    $deviceIp = Read-Host "Android 기기 IP 주소를 입력하세요 (건너뛰려면 Enter)"
}

if ($deviceIp) {
    Write-Host ""
    Write-Host "adb 연결 중: ${deviceIp}:5555"
    docker-compose -f docker-compose.dev.yml exec frontend adb connect "${deviceIp}:5555"

    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✅ adb 연결 성공" -ForegroundColor Green
        Write-Host ""

        # 4. 앱 빌드 및 설치 (선택)
        Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
        Write-Host ""
        $buildApp = Read-Host "앱을 빌드하고 설치하시겠습니까? (y/N)"

        if ($buildApp -match "^[Yy]$") {
            Write-Host ""
            Write-Host "4️⃣  앱 빌드 및 설치 중..." -ForegroundColor Yellow
            Write-Host ""
            docker-compose -f docker-compose.dev.yml exec frontend npm run android

            if ($LASTEXITCODE -eq 0) {
                Write-Host ""
                Write-Host "✅ 앱 설치 완료" -ForegroundColor Green
            } else {
                Write-Host ""
                Write-Host "❌ 앱 설치 실패" -ForegroundColor Red
            }
        }
    } else {
        Write-Host ""
        Write-Host "❌ adb 연결 실패" -ForegroundColor Red
        Write-Host ""
        Write-Host "문제 해결:"
        Write-Host "  .\scripts\adb-troubleshoot.ps1"
    }
} else {
    Write-Host "건너뛰기 - 나중에 수동으로 연결하세요:"
    Write-Host "  .\scripts\adb-connect.ps1 [IP주소]"
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "✨ 개발 환경 준비 완료!" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host ""
Write-Host "실행 중인 서비스:"
docker-compose -f docker-compose.dev.yml ps
Write-Host ""
Write-Host "로그 확인:"
Write-Host "  docker-compose -f docker-compose.dev.yml logs -f"
Write-Host ""
Write-Host "개발 시작하세요! 🎉" -ForegroundColor Green
Write-Host ""
