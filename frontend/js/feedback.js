/**
 * 통합 사용자 피드백 시스템
 * 로딩 상태, 메시지, 확인 다이얼로그 등을 관리
 */

class FeedbackSystem {
    constructor() {
        this.loadingStates = new Map();
        this.loadingOverlay = null;
        this.initializeStyles();
    }

    /**
     * 필요한 CSS 스타일을 동적으로 추가
     */
    initializeStyles() {
        if (!document.getElementById('feedback-styles')) {
            const styles = `
                <style id="feedback-styles">
                /* 로딩 오버레이 */
                .feedback-loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.3);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                    backdrop-filter: blur(2px);
                }

                .feedback-loading-content {
                    background: white;
                    border-radius: 12px;
                    padding: 30px;
                    text-align: center;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                    max-width: 300px;
                    width: 90%;
                }

                .feedback-spinner {
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #2196F3;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: feedback-spin 1s linear infinite;
                    margin: 0 auto 15px;
                }

                @keyframes feedback-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                .feedback-loading-text {
                    color: #424242;
                    font-size: 16px;
                    font-weight: 500;
                    margin: 0;
                }

                /* 버튼 로딩 상태 */
                .btn-loading {
                    position: relative;
                    pointer-events: none;
                    opacity: 0.7;
                }

                .btn-loading::after {
                    content: '';
                    position: absolute;
                    width: 16px;
                    height: 16px;
                    top: 50%;
                    left: 50%;
                    margin-left: -8px;
                    margin-top: -8px;
                    border: 2px solid transparent;
                    border-top: 2px solid #ffffff;
                    border-radius: 50%;
                    animation: feedback-spin 1s linear infinite;
                }

                /* 개선된 토스트 메시지 */
                .toast.enhanced {
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    font-weight: 500;
                    padding: 16px 24px;
                    min-width: 250px;
                }

                .toast.enhanced.success {
                    background: linear-gradient(135deg, #4CAF50, #45a049);
                }

                .toast.enhanced.error {
                    background: linear-gradient(135deg, #F44336, #e53935);
                }

                .toast.enhanced.warning {
                    background: linear-gradient(135deg, #FF9800, #f57c00);
                }

                .toast.enhanced.info {
                    background: linear-gradient(135deg, #2196F3, #1976d2);
                }

                /* 확인 다이얼로그 개선 */
                .feedback-confirm-dialog {
                    border-radius: 12px;
                    overflow: hidden;
                }

                .feedback-confirm-content {
                    padding: 24px;
                    text-align: center;
                }

                .feedback-confirm-icon {
                    font-size: 48px;
                    margin-bottom: 16px;
                }

                .feedback-confirm-icon.warning {
                    color: #FF9800;
                }

                .feedback-confirm-icon.danger {
                    color: #F44336;
                }

                .feedback-confirm-icon.info {
                    color: #2196F3;
                }

                .feedback-confirm-title {
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 12px;
                    color: #424242;
                }

                .feedback-confirm-message {
                    font-size: 16px;
                    color: #666;
                    line-height: 1.5;
                    margin-bottom: 24px;
                }

                .feedback-confirm-buttons {
                    display: flex;
                    gap: 12px;
                    justify-content: center;
                }

                @media only screen and (max-width: 600px) {
                    .feedback-confirm-buttons {
                        flex-direction: column;
                    }

                    .feedback-confirm-buttons .btn {
                        width: 100%;
                        margin: 0 0 8px 0;
                    }
                }
                </style>
            `;
            document.head.insertAdjacentHTML('beforeend', styles);
        }
    }

    /**
     * 개선된 메시지 표시
     * @param {string} text - 메시지 텍스트
     * @param {string} type - 메시지 타입 (success, error, warning, info)
     * @param {number} duration - 표시 시간 (밀리초, 기본 4000)
     * @param {object} options - 추가 옵션
     */
    showMessage(text, type = 'info', duration = 4000, options = {}) {
        const typeMap = {
            success: 'success',
            error: 'error',
            warning: 'warning',
            info: 'info'
        };

        const colorClass = typeMap[type] || 'info';

        // 아이콘 추가
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const icon = options.icon || icons[type] || '';
        const messageText = icon ? `${icon} ${text}` : text;

        M.toast({
            html: messageText,
            classes: `enhanced ${colorClass} white-text`,
            displayLength: duration,
            ...options
        });
    }

    /**
     * 전역 로딩 상태 표시
     * @param {string} message - 로딩 메시지
     * @param {string} id - 로딩 상태 식별자
     */
    showLoading(message = '처리 중...', id = 'global') {
        // 기존 로딩이 있으면 메시지만 업데이트
        if (this.loadingOverlay && this.loadingStates.has(id)) {
            const textElement = this.loadingOverlay.querySelector('.feedback-loading-text');
            if (textElement) {
                textElement.textContent = message;
            }
            return;
        }

        this.loadingStates.set(id, message);

        if (!this.loadingOverlay) {
            this.loadingOverlay = document.createElement('div');
            this.loadingOverlay.className = 'feedback-loading-overlay';
            this.loadingOverlay.innerHTML = `
                <div class="feedback-loading-content">
                    <div class="feedback-spinner"></div>
                    <p class="feedback-loading-text">${message}</p>
                </div>
            `;
            document.body.appendChild(this.loadingOverlay);

            // 버튼 클릭 방지
            this.loadingOverlay.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        }
    }

    /**
     * 전역 로딩 상태 숨기기
     * @param {string} id - 로딩 상태 식별자
     */
    hideLoading(id = 'global') {
        this.loadingStates.delete(id);

        // 모든 로딩 상태가 제거되면 오버레이 숨기기
        if (this.loadingStates.size === 0 && this.loadingOverlay) {
            this.loadingOverlay.remove();
            this.loadingOverlay = null;
        }
    }

    /**
     * 버튼 로딩 상태 표시
     * @param {string|HTMLElement} button - 버튼 선택자 또는 요소
     * @param {boolean} loading - 로딩 상태
     */
    setButtonLoading(button, loading = true) {
        const btnElement = typeof button === 'string' ? document.querySelector(button) : button;
        if (!btnElement) return;

        if (loading) {
            btnElement.classList.add('btn-loading');
            btnElement.setAttribute('data-original-text', btnElement.textContent);
            btnElement.textContent = '';
        } else {
            btnElement.classList.remove('btn-loading');
            const originalText = btnElement.getAttribute('data-original-text');
            if (originalText) {
                btnElement.textContent = originalText;
                btnElement.removeAttribute('data-original-text');
            }
        }
    }

    /**
     * 확인 다이얼로그 표시
     * @param {object} options - 다이얼로그 옵션
     * @returns {Promise<boolean>} - 사용자 응답
     */
    confirm(messageOrOptions = {}, onConfirmCallback = null, onCancelCallback = null) {
        // 콜백 형태 지원: confirm(message, onConfirm, onCancel)
        if (typeof messageOrOptions === 'string') {
            const message = messageOrOptions;
            return this._showConfirmDialog({
                message: message,
                onConfirm: onConfirmCallback,
                onCancel: onCancelCallback
            });
        }

        // Promise 형태 지원: confirm(options).then(...)
        return this._showConfirmDialog(messageOrOptions);
    }

    _showConfirmDialog(options = {}) {
        const {
            title = '확인',
            message = '작업을 계속하시겠습니까?',
            type = 'warning', // warning, danger, info
            confirmText = '확인',
            cancelText = '취소',
            confirmClass = 'btn-flat',
            cancelClass = 'btn-flat',
            onConfirm = null,
            onCancel = null
        } = options;

        const icons = {
            warning: '⚠️',
            danger: '🗑️',
            info: 'ℹ️'
        };

        const modalId = 'feedback-confirm-modal-' + Date.now();
        const modalHtml = `
            <div id="${modalId}" class="modal feedback-confirm-dialog">
                <div class="modal-content feedback-confirm-content">
                    <div class="feedback-confirm-icon ${type}">${icons[type] || '❓'}</div>
                    <h4 class="feedback-confirm-title">${title}</h4>
                    <p class="feedback-confirm-message">${message}</p>
                    <div class="feedback-confirm-buttons">
                        <button class="btn ${cancelClass} grey" data-action="cancel">${cancelText}</button>
                        <button class="btn ${confirmClass} ${type === 'danger' ? 'red' : 'blue'}" data-action="confirm">${confirmText}</button>
                    </div>
                </div>
            </div>
        `;

        // 모달을 DOM에 추가
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = document.getElementById(modalId);
        const modalInstance = M.Modal.init(modal, {
            dismissible: false,
            onCloseEnd: () => {
                modal.remove();
            }
        });

        // 콜백 또는 Promise 지원
        if (onConfirm || onCancel) {
            // 콜백 형태
            modal.addEventListener('click', (e) => {
                const action = e.target.getAttribute('data-action');
                if (action) {
                    modalInstance.close();
                    if (action === 'confirm' && onConfirm) {
                        onConfirm();
                    } else if (action === 'cancel' && onCancel) {
                        onCancel();
                    }
                }
            });
        } else {
            // Promise 형태
            return new Promise((resolve) => {
                modal.addEventListener('click', (e) => {
                    const action = e.target.getAttribute('data-action');
                    if (action) {
                        modalInstance.close();
                        resolve(action === 'confirm');
                    }
                });
            });
        }

        modalInstance.open();
    }

    /**
     * API 요청 래퍼 (자동 로딩 및 에러 처리)
     * @param {function} apiCall - API 호출 함수
     * @param {object} options - 옵션
     * @returns {Promise} - API 응답
     */
    async apiRequest(apiCall, options = {}) {
        const {
            loadingMessage = '처리 중...',
            loadingId = 'api-request',
            showSuccess = true,
            successMessage = '완료되었습니다.',
            showError = true,
            button = null
        } = options;

        try {
            // 로딩 시작
            this.showLoading(loadingMessage, loadingId);
            if (button) {
                this.setButtonLoading(button, true);
            }

            // API 호출
            const result = await apiCall();

            // 성공 메시지
            if (showSuccess && result && result.success) {
                this.showMessage(successMessage, 'success');
            }

            return result;

        } catch (error) {
            // 에러 메시지
            if (showError) {
                const errorMessage = error.message || '오류가 발생했습니다.';
                this.showMessage(errorMessage, 'error');
            }
            throw error;

        } finally {
            // 로딩 종료
            this.hideLoading(loadingId);
            if (button) {
                this.setButtonLoading(button, false);
            }
        }
    }

    /**
     * 입력 유효성 검사 피드백
     * @param {string|HTMLElement} input - 입력 필드
     * @param {boolean} isValid - 유효성 여부
     * @param {string} message - 메시지
     */
    setInputValidation(input, isValid, message = '') {
        const inputElement = typeof input === 'string' ? document.querySelector(input) : input;
        if (!inputElement) return;

        // Materialize CSS 클래스 적용
        if (isValid) {
            inputElement.classList.remove('invalid');
            inputElement.classList.add('valid');
        } else {
            inputElement.classList.remove('valid');
            inputElement.classList.add('invalid');
        }

        // 헬퍼 텍스트 업데이트
        const helperText = inputElement.parentNode.querySelector('.helper-text');
        if (helperText && message) {
            helperText.textContent = message;
            helperText.setAttribute('data-error', message);
        }
    }

    /**
     * 폼 유효성 검사
     * @param {string|HTMLElement} form - 폼 선택자 또는 요소
     * @param {object} rules - 유효성 검사 규칙
     * @returns {boolean} - 유효성 여부
     */
    validateForm(form, rules = {}) {
        const formElement = typeof form === 'string' ? document.querySelector(form) : form;
        if (!formElement) return false;

        let isValid = true;
        const formData = new FormData(formElement);

        Object.keys(rules).forEach(fieldName => {
            const rule = rules[fieldName];
            const value = formData.get(fieldName);
            const input = formElement.querySelector(`[name="${fieldName}"]`);

            let fieldValid = true;
            let message = '';

            // 필수 필드 검사
            if (rule.required && (!value || value.trim() === '')) {
                fieldValid = false;
                message = rule.requiredMessage || '필수 입력 항목입니다.';
            }

            // 최소/최대 길이 검사
            if (fieldValid && value) {
                if (rule.minLength && value.length < rule.minLength) {
                    fieldValid = false;
                    message = rule.minLengthMessage || `최소 ${rule.minLength}자 이상 입력해주세요.`;
                }
                if (rule.maxLength && value.length > rule.maxLength) {
                    fieldValid = false;
                    message = rule.maxLengthMessage || `최대 ${rule.maxLength}자까지 입력 가능합니다.`;
                }
            }

            // 커스텀 유효성 검사
            if (fieldValid && rule.validator && typeof rule.validator === 'function') {
                const customResult = rule.validator(value);
                if (customResult !== true) {
                    fieldValid = false;
                    message = typeof customResult === 'string' ? customResult : '올바른 값을 입력해주세요.';
                }
            }

            this.setInputValidation(input, fieldValid, message);
            if (!fieldValid) {
                isValid = false;
            }
        });

        return isValid;
    }
}

// 전역 인스턴스 생성
window.Feedback = new FeedbackSystem();

// 기존 showMessage 함수와의 호환성을 위한 래퍼
window.showMessage = function(text, type, duration) {
    window.Feedback.showMessage(text, type, duration);
};