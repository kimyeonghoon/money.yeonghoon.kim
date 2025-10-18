# Claude Code 설정

이 디렉토리에는 Claude Code 관련 설정과 훅이 포함되어 있습니다.

## 📂 구조

```
.claude/
├── settings.json              # 프로젝트 전체 설정 (Git 추적)
├── settings.local.json        # 개인 로컬 설정 (Git 제외)
├── hooks/                     # 훅 스크립트
│   └── load-collaboration-rules.sh
└── README.md                  # 이 파일
```

## 🪝 Hooks

### SessionStart Hook - 협업 규칙 자동 로드

Claude 세션 시작 시 자동으로 `CLAUDE.md` 파일을 읽어서 협업 규칙을 Claude에게 전달합니다.

**설정 위치:** `.claude/settings.json`

**훅 스크립트:** `.claude/hooks/load-collaboration-rules.sh`

**작동 방식:**
1. Claude Code 세션이 시작될 때 자동 실행
2. 프로젝트 루트의 `CLAUDE.md` 파일 확인
3. 파일이 존재하면 내용을 읽어서 Claude에게 추가 컨텍스트로 전달
4. Claude가 세션 동안 해당 규칙을 준수

**장점:**
- ✅ 매 세션마다 수동으로 CLAUDE.md를 언급할 필요 없음
- ✅ 협업 규칙이 자동으로 적용됨
- ✅ 일관된 코드 품질 유지

## ⚙️ 설정 파일

### `settings.json` (프로젝트 전체)

프로젝트 전체에 적용되는 설정으로, Git에 커밋됩니다.
모든 팀원이 동일한 훅과 설정을 공유합니다.

```json
{
  "hooks": {
    "SessionStart": [
      {
        "hooks": [
          {
            "type": "command",
            "command": "bash \"$CLAUDE_PROJECT_DIR\"/.claude/hooks/load-collaboration-rules.sh"
          }
        ]
      }
    ]
  }
}
```

### `settings.local.json` (개인 설정)

개인적으로 사용하는 설정으로, Git에서 제외됩니다.
팀원마다 다른 설정을 가질 수 있습니다.

**예시:**
```json
{
  "hooks": {
    "PostToolUse": [
      {
        "matcher": "Write|Edit",
        "hooks": [
          {
            "type": "command",
            "command": "echo '✅ 파일이 수정되었습니다'"
          }
        ]
      }
    ]
  }
}
```

## 💻 Windows 사용자

Windows에서 훅이 제대로 작동하려면 **Git Bash** 또는 **WSL**이 필요합니다:

### 방법 1: Git Bash (권장)
```bash
# Git for Windows 설치 시 bash가 함께 설치됨
# 추가 설정 불필요
```

### 방법 2: WSL
```bash
# WSL 설치 확인
wsl --version

# WSL에서 bash 사용
# 추가 설정 불필요
```

## 🔒 보안 주의사항

**⚠️  훅은 자동으로 Shell 명령을 실행합니다!**

- 훅 스크립트를 추가하기 전에 항상 내용을 검토하세요
- 신뢰할 수 없는 소스의 훅은 사용하지 마세요
- `.claude/hooks/` 디렉토리의 스크립트는 실행 권한이 필요합니다

**실행 권한 설정 (Linux/macOS):**
```bash
chmod +x .claude/hooks/*.sh
```

## 📚 추가 정보

- [Claude Code 훅 문서](https://docs.claude.com/en/docs/claude-code/hooks)
- [프로젝트 협업 규칙](../CLAUDE.md)

## 🛠️ 커스터마이징

프로젝트 요구사항에 맞게 추가 훅을 설정할 수 있습니다:

**예시 - 코드 스타일 자동 검사:**
```json
{
  "hooks": {
    "PostToolUse": [
      {
        "matcher": "Write|Edit",
        "hooks": [
          {
            "type": "command",
            "command": "bash \"$CLAUDE_PROJECT_DIR\"/.claude/hooks/check-style.sh"
          }
        ]
      }
    ]
  }
}
```

**지원되는 훅 이벤트:**
- `SessionStart`: 세션 시작 시
- `UserPromptSubmit`: 사용자 프롬프트 제출 시
- `PreToolUse`: 도구 사용 전
- `PostToolUse`: 도구 사용 후
- `Notification`: 알림 발생 시

---

**Happy Coding! 🚀**
