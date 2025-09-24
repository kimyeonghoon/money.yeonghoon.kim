# Money Management Application

자산 및 지출 관리 웹 애플리케이션

## 개발환경 설정

### 1. 환경변수 설정
```bash
cp .env.example .env
# .env 파일을 열어서 필요한 값들을 설정하세요
```

### 2. Docker 컨테이너 실행
```bash
cd docker
docker-compose up -d
```

### 3. 서비스 접근
- API 서버: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (개발 모드)

### 4. 개발 모드로 phpMyAdmin 실행
```bash
docker-compose --profile dev up -d
```

## API 엔드포인트

- `GET /api/` - API 정보
- `GET /api/health` - Health Check

## 개발 명령어

### Docker 컨테이너 관리
```bash
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f

# MySQL 데이터 초기화 (주의: 모든 데이터 삭제)
docker-compose down -v
docker-compose up -d
```

### 데이터베이스 접근
```bash
# MySQL 컨테이너 접속
docker exec -it money_mysql mysql -u root -p

# 또는 phpMyAdmin 사용: http://localhost:8081
```

## 폴더 구조
```
├── backend/          # PHP API 서버
├── frontend/         # 정적 웹 자산
├── docker/           # Docker 설정
└── logs/             # 로그 파일
```