<?php
$pageTitle = '자산현황';
include 'includes/header.php';
?>

<!-- Inline editing styles -->
<style>
    .balance-cell.editable:hover {
        background-color: #e3f2fd !important;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }

    .balance-cell.editable {
        position: relative;
    }

    .balance-cell.editable::after {
        content: "✏️";
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.2s ease;
        position: absolute;
        right: -20px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .balance-cell.editable:hover::after {
        opacity: 0.7;
    }

    /* 모바일 카드에서 잔액 컨테이너 우측 공간 확보 */
    .asset-card-balance.balance-cell.editable {
        padding-right: 0;
        text-align: right;
        position: relative;
        display: block;
    }

    .balance-input {
        font-family: 'Roboto', sans-serif !important;
    }


    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .section-header .section-title {
        margin: 0;
        flex: 1;
    }

    .section-header-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    /* 모바일에서 section-header 최적화 */
    @media only screen and (max-width: 600px) {
        .section-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            margin-bottom: 20px;
        }

        .section-header .section-title {
            text-align: center;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .section-header-actions {
            justify-content: center;
            gap: 12px;
        }

        .section-header .btn-small {
            font-size: 12px;
            padding: 8px 16px;
            min-height: 40px;
            white-space: nowrap;
            border-radius: 20px;
        }

        .section-header .btn-floating {
            width: 40px;
            height: 40px;
            transform: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .section-header .btn-floating .material-icons {
            font-size: 20px;
            line-height: 1;
            margin: 0;
        }
    }

    @media only screen and (max-width: 480px) {
        .section-header .section-title {
            font-size: 16px;
        }

        .section-header-actions {
            gap: 8px;
        }

        .section-header .btn-small {
            font-size: 11px;
            padding: 6px 14px;
            min-height: 36px;
            white-space: nowrap;
            border-radius: 18px;
        }

        .section-header .btn-floating {
            width: 40px;
            height: 40px;
            transform: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .section-header .btn-floating .material-icons {
            font-size: 20px;
            line-height: 1;
            margin: 0;
        }

        /* 모바일에서 버튼 텍스트 숨기기 */
        .section-header .btn-small .button-text {
            display: none;
        }

        .section-header .btn-small {
            min-width: 40px;
            min-height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .section-header .btn-small .material-icons {
            margin: 0;
            font-size: 20px;
            line-height: 1;
        }
    }

    .asset-row {
        transition: background-color 0.2s ease;
        user-select: none;
        cursor: pointer;
    }

    .asset-row:hover {
        background-color: #f8f9fa !important;
    }

    .asset-row.long-press-active {
        background-color: #e3f2fd !important;
        animation: pulse 0.5s ease-in-out;
    }

    /* 드래그 앤 드롭 스타일 */
    .sortable-enabled .asset-row {
        cursor: move;
        cursor: grab;
    }

    .sortable-enabled .asset-row:active {
        cursor: grabbing;
    }

    /* 일반 모드에서는 pointer 커서 */
    .asset-row:not(.sortable-enabled) {
        cursor: pointer !important;
    }

    .ui-sortable-helper {
        background-color: #fff3e0 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transform: rotate(2deg);
    }

    .ui-sortable-placeholder {
        background-color: #f5f5f5 !important;
        border: 2px dashed #ccc !important;
        height: 50px;
        visibility: visible !important;
    }

    .drag-handle {
        color: #999;
        cursor: grab;
        padding: 5px;
        display: inline-block;
        opacity: 0.7;
        transition: opacity 0.3s;
    }

    .sortable-enabled .drag-handle {
        opacity: 1;
    }

    .drag-handle:hover {
        color: #666;
        opacity: 1;
    }

    .reorder-toggle {
        margin-left: 10px;
    }

    /* 반응형 디스플레이 */
    @media (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }
    }

    @media (min-width: 769px) {
        .mobile-only {
            display: none !important;
        }
    }

    /* 모바일 카드 스타일 */
    .asset-card {
        background: #fff;
        border-radius: 8px;
        margin-bottom: 12px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #2196F3;
        transition: all 0.3s ease;
        user-select: none;
        cursor: pointer;
    }

    .asset-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }

    .asset-card.long-press-active {
        background-color: #e3f2fd !important;
        transform: scale(1.02);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .sortable-enabled .asset-card {
        cursor: move;
        cursor: grab;
    }

    .sortable-enabled .asset-card:active {
        cursor: grabbing;
    }

    /* 일반 모드에서는 pointer 커서 */
    .asset-card:not(.sortable-enabled) {
        cursor: pointer !important;
    }

    .asset-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 8px;
    }

    .asset-card-title {
        font-weight: bold;
        font-size: 1.1em;
        color: #2196F3;
    }

    .asset-card-type {
        background: #f5f5f5;
        color: #666;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .asset-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .asset-card-row:last-child {
        margin-bottom: 0;
    }

    .asset-card-label {
        color: #666;
        font-size: 0.9em;
        min-width: 60px;
    }

    .asset-card-value {
        color: #333;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }

    .asset-card-balance {
        font-weight: bold;
        color: #4CAF50;
        font-size: 1.1em;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background-color 0.3s;
        text-align: right;
    }

    .asset-card-balance:hover {
        background-color: #f0f8f0;
    }

    .asset-card-percentage {
        color: #666;
        font-size: 0.9em;
    }

    .mobile-drag-handle {
        color: #999;
        opacity: 0.7;
        transition: opacity 0.3s;
        display: none;
    }

    .sortable-enabled .mobile-drag-handle {
        display: block;
        opacity: 1;
    }

    @keyframes pulse {
        0% { background-color: #e3f2fd; }
        50% { background-color: #bbdefb; }
        100% { background-color: #e3f2fd; }
    }

    .row-hint {
        font-size: 10px;
        color: #999;
        text-align: center;
        margin-top: 5px;
        font-style: italic;
    }
</style>

    <main class="container">
        <div class="section">
            <div class="row">
                <div class="col s12">
                </div>
            </div>

            <!-- 로딩 -->
            <div id="loading" class="row">
                <div class="col s12 center-align">
                    <div class="preloader-wrapper big active">
                        <div class="spinner-layer spinner-blue-only">
                            <div class="circle-clipper left">
                                <div class="circle"></div>
                            </div>
                            <div class="gap-patch">
                                <div class="circle"></div>
                            </div>
                            <div class="circle-clipper right">
                                <div class="circle"></div>
                            </div>
                        </div>
                    </div>
                    <p>데이터를 불러오는 중입니다...</p>
                </div>
            </div>

            <!-- 자산현황 컨텐츠 -->
            <div id="dashboard-content" style="display: none;">
                <!-- 월별 선택기 -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-content center-align">
                            <h5 style="margin-bottom: 15px;">📅 조회 기간</h5>
                            <div class="row">
                                <div class="col s12 m6 offset-m3">
                                    <div class="input-field">
                                        <select id="assets-month-selector">
                                            <!-- 동적으로 생성됨 -->
                                        </select>
                                        <label>조회 월 선택</label>
                                    </div>
                                </div>
                            </div>
                            <div id="assets-archive-controls" style="display: none; margin-top: 10px;">
                                <button class="btn blue" id="edit-assets-archive-btn">
                                    <i class="material-icons left">edit</i>아카이브 수정
                                </button>
                                <button class="btn green" id="create-assets-archive-btn" style="margin-left: 10px;">
                                    <i class="material-icons left">archive</i>스냅샷 생성
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 총자산현황 -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-content">
                            <h5 class="section-title center-align" style="margin-bottom: 20px;">💰 총자산현황</h5>
                            <div class="row" style="margin-bottom: 10px;">
                                <div class="col s12 m4">
                                    <div class="center-align">
                                        <h6 style="color: #1976d2; margin: 0;">현금성 자산</h6>
                                        <span id="total-cash-assets" style="font-size: 18px; font-weight: bold;">-</span>
                                    </div>
                                </div>
                                <div class="col s12 m4">
                                    <div class="center-align">
                                        <h6 style="color: #388e3c; margin: 0;">저축+투자 자산</h6>
                                        <span id="total-investment-assets" style="font-size: 18px; font-weight: bold;">-</span>
                                    </div>
                                </div>
                                <div class="col s12 m4">
                                    <div class="center-align">
                                        <h6 style="color: #f57c00; margin: 0;">연금 자산</h6>
                                        <span id="total-pension-assets" style="font-size: 18px; font-weight: bold;">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="divider" style="margin: 15px 0;"></div>
                            <div class="center-align">
                                <h6 style="color: #424242; margin: 0;">총합계</h6>
                                <span id="total-all-assets" style="font-size: 24px; font-weight: bold; color: #1976d2;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 현금성 자산 상세 -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5 class="section-title">💵 현금성 자산</h5>
                        <div class="section-header-actions">
                            <button id="reorder-toggle" class="btn-small waves-effect waves-light blue reorder-toggle" title="순서 변경">
                                <i class="material-icons left">swap_vert</i><span class="button-text">순서변경</span>
                            </button>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-asset-modal" title="자산 추가">
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <!-- 데스크톱용 테이블 -->
                            <div class="responsive-table desktop-only">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>계좌</th>
                                            <th>종목명</th>
                                            <th>잔액</th>
                                            <th>비중</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cash-assets-detail-table">
                                        <tr>
                                            <td colspan="5" class="center-align">데이터를 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 모바일용 카드 -->
                            <div class="mobile-only" id="cash-assets-detail-cards">
                                <div class="center-align">데이터를 불러오는 중...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저축 + 투자 자산 상세 -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5 class="section-title">📈 저축 + 투자 자산</h5>
                        <div class="section-header-actions">
                            <button id="investment-reorder-toggle" class="btn-small waves-effect waves-light blue reorder-toggle" title="순서 변경">
                                <i class="material-icons left">swap_vert</i><span class="button-text">순서변경</span>
                            </button>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-investment-modal" title="자산 추가">
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <!-- 데스크톱용 테이블 -->
                            <div class="responsive-table desktop-only">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>계좌</th>
                                            <th>종목명</th>
                                            <th>잔액</th>
                                            <th>비중</th>
                                        </tr>
                                    </thead>
                                    <tbody id="investment-assets-detail-table">
                                        <tr>
                                            <td colspan="5" class="center-align">데이터를 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 모바일용 카드 -->
                            <div class="mobile-only" id="investment-assets-detail-cards">
                                <div class="center-align">데이터를 불러오는 중...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저축 + 투자 자산 비중 -->
                <div class="dashboard-section">
                    <div class="card">
                        <div class="card-content">
                            <h6 class="section-title" style="margin-bottom: 15px;">📊 자산 비중(연금자산 제외)</h6>

                            <!-- 데스크톱용 테이블 -->
                            <div class="responsive-table desktop-only">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>자산군</th>
                                            <th>잔액</th>
                                            <th>비중</th>
                                        </tr>
                                    </thead>
                                    <tbody id="asset-allocation-table">
                                        <tr>
                                            <td colspan="3" class="center-align">데이터를 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 모바일용 카드 -->
                            <div class="mobile-only" id="asset-allocation-cards">
                                <div class="center-align">데이터를 불러오는 중...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 연금자산 상세 -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h5 class="section-title">🛡️ 연금자산</h5>
                        <div class="section-header-actions">
                            <button id="pension-reorder-toggle" class="btn-small waves-effect waves-light blue reorder-toggle" title="순서 변경">
                                <i class="material-icons left">swap_vert</i><span class="button-text">순서변경</span>
                            </button>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-pension-modal" title="연금자산 추가">
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-content">
                            <!-- 데스크톱용 테이블 -->
                            <div class="responsive-table desktop-only">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>구분</th>
                                            <th>계좌</th>
                                            <th>종목명</th>
                                            <th>평가금액</th>
                                            <th>납입잔액</th>
                                            <th>수익률</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pension-assets-detail-table">
                                        <tr>
                                            <td colspan="6" class="center-align">데이터를 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- 모바일용 카드 -->
                            <div class="mobile-only" id="pension-assets-detail-cards">
                                <div class="center-align">데이터를 불러오는 중...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 에러 메시지 -->
            <div id="error-message" class="card red white-text" style="display: none;">
                <div class="card-content">
                    <span class="card-title">오류 발생</span>
                    <p>데이터를 불러오는 중 오류가 발생했습니다. 새로고침 후 다시 시도해주세요.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- 자산 수정 모달 -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h4><i class="material-icons left">edit</i>자산 정보 수정</h4>
            <div class="row">
                <form id="edit-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-account" type="text" maxlength="100">
                            <label for="edit-account">계좌명</label>
                            <span class="helper-text">예: OK저축은행, 집, 카카오뱅크 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-item-name" type="text" maxlength="200" required>
                            <label for="edit-item-name">종목명 *</label>
                            <span class="helper-text">예: 짠테크통장, 금고, 적금 등</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button id="delete-asset" class="waves-effect waves-light btn red left">
                <i class="material-icons left">delete</i>삭제
            </button>
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-edit" class="waves-effect waves-light btn blue">
                <i class="material-icons left">save</i>저장
            </button>
        </div>
    </div>

    <!-- 자산 추가 모달 -->
    <div id="add-asset-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">add</i>현금성 자산 추가</h4>
            <div class="row">
                <form id="add-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-account" type="text" maxlength="100">
                            <label for="add-account">계좌명</label>
                            <span class="helper-text">예: OK저축은행, 집, 카카오뱅크 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-item-name" type="text" maxlength="200" required>
                            <label for="add-item-name">종목명 *</label>
                            <span class="helper-text">예: 짠테크통장, 금고, 적금 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-balance" type="number" min="0" step="1000" value="0" required>
                            <label for="add-balance">초기 잔액 *</label>
                            <span class="helper-text">단위: 원</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-add" class="waves-effect waves-light btn green">
                <i class="material-icons left">add</i>추가
            </button>
        </div>
    </div>

    <!-- 투자자산 추가 모달 -->
    <div id="add-investment-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">trending_up</i>저축 + 투자 자산 추가</h4>
            <div class="row">
                <form id="add-investment-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <select id="add-investment-type">
                                <option value="" disabled selected>선택하세요</option>
                                <option value="저축">💰 저축</option>
                                <option value="혼합">🏦 혼합</option>
                                <option value="주식">📈 주식</option>
                            </select>
                            <label>투자유형 *</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="add-investment-account" type="text" maxlength="100">
                            <label for="add-investment-account">계좌명</label>
                            <span class="helper-text">예: KB증권, ISA계좌 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-investment-item-name" type="text" maxlength="200" required>
                            <label for="add-investment-item-name">종목명 *</label>
                            <span class="helper-text">예: Vanguard S&P 500 ETF, KB증권 중개형 ISA 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-investment-balance" type="number" min="0" step="1000" value="0" required>
                            <label for="add-investment-balance">현재 잔액 *</label>
                            <span class="helper-text">단위: 원</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-investment-add" class="waves-effect waves-light btn green">
                <i class="material-icons left">add</i>추가
            </button>
        </div>
    </div>

    <!-- 연금자산 추가 모달 -->
    <div id="add-pension-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">security</i>연금자산 추가</h4>
            <div class="row">
                <form id="add-pension-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <select id="add-pension-type">
                                <option value="" disabled selected>선택하세요</option>
                                <option value="연금저축">💰 연금저축</option>
                                <option value="퇴직연금">🏢 퇴직연금</option>
                            </select>
                            <label>연금유형 *</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="add-pension-account" type="text" maxlength="100">
                            <label for="add-pension-account">계좌명</label>
                            <span class="helper-text">예: 미래에셋, KB증권 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="add-pension-item-name" type="text" maxlength="200" required>
                            <label for="add-pension-item-name">종목명 *</label>
                            <span class="helper-text">예: KODEX 미국나스닥100, TIGER 미국나스닥100TR채권혼합Fn</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <input id="add-pension-current-value" type="number" min="0" step="1000" value="0" required>
                            <label for="add-pension-current-value">평가금액 *</label>
                            <span class="helper-text">단위: 원</span>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="add-pension-deposit-amount" type="number" min="0" step="1000" value="0" required>
                            <label for="add-pension-deposit-amount">납입잔액 *</label>
                            <span class="helper-text">단위: 원</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-pension-add" class="waves-effect waves-light btn green">
                <i class="material-icons left">add</i>추가
            </button>
        </div>
    </div>

    <!-- 연금자산 편집 모달 -->
    <div id="edit-pension-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">edit</i>연금자산 편집</h4>
            <div class="row">
                <form id="edit-pension-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <select id="edit-pension-type">
                                <option value="연금저축">💰 연금저축</option>
                                <option value="퇴직연금">🏢 퇴직연금</option>
                            </select>
                            <label>연금유형 *</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="edit-pension-account" type="text" maxlength="100">
                            <label for="edit-pension-account">계좌명</label>
                            <span class="helper-text">예: 미래에셋, KB증권 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-pension-item-name" type="text" maxlength="200" required>
                            <label for="edit-pension-item-name">종목명 *</label>
                            <span class="helper-text">예: KODEX S&P500, TIGER 미국나스닥100 등</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button id="delete-pension-asset" class="waves-effect waves-light btn red left">
                <i class="material-icons left">delete</i>삭제
            </button>
            <button class="btn-flat modal-close">취소</button>
            <button id="save-pension-edit" class="btn waves-effect waves-light purple">
                <i class="material-icons left">save</i>저장
            </button>
        </div>
    </div>

<script>
$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.getElementById('edit-modal'));
    M.Modal.init(document.getElementById('add-asset-modal'));
    M.Modal.init(document.getElementById('add-investment-modal'));
    M.Modal.init(document.getElementById('add-pension-modal'));
    M.Modal.init(document.getElementById('edit-pension-modal'));

    // Select 초기화
    M.FormSelect.init(document.getElementById('add-investment-type'));
    M.FormSelect.init(document.getElementById('add-pension-type'));
    M.FormSelect.init(document.getElementById('edit-pension-type'));

    // 월별 선택기 초기화
    initAssetsMonthSelector();

    // 저장 버튼 이벤트 핸들러
    $('#save-edit').on('click', function() {
        saveEditedAsset();
    });

    // 추가 버튼 이벤트 핸들러
    $('#save-add').on('click', function() {
        saveNewAsset();
    });

    // 투자자산 추가 버튼 이벤트 핸들러
    $('#save-investment-add').on('click', function() {
        saveNewInvestmentAsset();
    });

    // 연금자산 추가 버튼 이벤트 핸들러
    $('#save-pension-add').on('click', function() {
        saveNewPensionAsset();
    });

    // 연금자산 편집 저장 버튼 이벤트 핸들러
    $('#save-pension-edit').on('click', function() {
        saveEditedPensionAsset();
    });

    // 자산 삭제 버튼 이벤트 핸들러
    $('#delete-asset').on('click', function() {
        deleteAsset();
    });

    // 연금자산 삭제 버튼 이벤트 핸들러
    $('#delete-pension-asset').on('click', function() {
        deletePensionAsset();
    });

    // 순서 변경 토글 버튼 이벤트 핸들러
    $('#reorder-toggle').on('click', function() {
        toggleReorderMode();
    });

    // 투자자산 순서 변경 토글 버튼 이벤트 핸들러
    $('#investment-reorder-toggle').on('click', function() {
        toggleInvestmentReorderMode();
    });

    // 연금자산 순서 변경 토글 버튼 이벤트 핸들러
    $('#pension-reorder-toggle').on('click', function() {
        togglePensionReorderMode();
    });

    // 월별 선택기 변경 이벤트
    $('#assets-month-selector').on('change', function() {
        const selectedMonth = $(this).val();
        loadAssetsMonthData(selectedMonth);
    });

    // 아카이브 수정 버튼
    $('#edit-assets-archive-btn').on('click', function() {
        editAssetsArchiveData();
    });

    // 스냅샷 생성 버튼
    $('#create-assets-archive-btn').on('click', function() {
        createAssetsMonthlySnapshot();
    });

    // 현재 월 데이터 로드
    loadCurrentAssetsData();
});

let assetsCurrentViewMode = 'current'; // 'current' or 'archive'
let assetsCurrentSelectedMonth = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0');

function initAssetsMonthSelector() {
    const monthSelector = $('#assets-month-selector');
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;

    // 현재 월부터 과거 12개월까지 생성
    for (let i = 0; i < 12; i++) {
        const targetDate = new Date(currentYear, currentMonth - 1 - i, 1);
        const year = targetDate.getFullYear();
        const month = targetDate.getMonth() + 1;
        const value = year + '-' + String(month).padStart(2, '0');
        const text = year + '년 ' + month + '월';
        const isSelected = i === 0 ? 'selected' : '';

        monthSelector.append(`<option value="${value}" ${isSelected}>${text}</option>`);
    }

    M.FormSelect.init(document.querySelectorAll('select'));
}

function loadCurrentAssetsData() {
    assetsCurrentViewMode = 'current';
    $('#assets-archive-controls').hide();
    $('.section-header-actions').show(); // 추가 버튼들 표시
    loadCashAssets();
    loadInvestmentAssets();
    loadPensionAssets();
}

function loadAssetsMonthData(selectedMonth) {
    assetsCurrentSelectedMonth = selectedMonth;
    const currentYearMonth = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0');

    if (selectedMonth === currentYearMonth) {
        // 현재 월 - 실시간 데이터
        assetsCurrentViewMode = 'current';
        $('#assets-archive-controls').hide();
        $('.section-header-actions').show();
        loadCashAssets();
        loadInvestmentAssets();
        loadPensionAssets();
    } else {
        // 과거 월 - 아카이브 데이터
        assetsCurrentViewMode = 'archive';
        $('#assets-archive-controls').show();
        $('.section-header-actions').hide(); // 추가 버튼들 숨김
        loadAssetsArchiveData(selectedMonth);
    }
}

function loadAssetsArchiveData(month) {
    const [year, monthNum] = month.split('-');

    // 아카이브된 자산 스냅샷 데이터 로드
    $.ajax({
        url: `http://localhost:8080/api/monthly-snapshots/assets?year=${year}&month=${parseInt(monthNum)}`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data && response.data.length > 0) {
                displayArchiveAssetsData(response.data);
                $('#loading').hide();
                $('#dashboard-content').show();
            } else {
                displayNoAssetsArchiveMessage();
                $('#loading').hide();
                $('#dashboard-content').show();
            }
        },
        error: function() {
            displayNoAssetsArchiveMessage();
            $('#loading').hide();
            $('#dashboard-content').show();
        }
    });
}

function displayArchiveAssetsData(snapshots) {
    // 자산 타입별로 데이터 분류
    let cashSnapshot = snapshots.find(s => s.asset_type === '현금성');
    let investmentSnapshot = snapshots.find(s => s.asset_type === '투자');
    let pensionSnapshot = snapshots.find(s => s.asset_type === '연금');

    // 총자산현황 업데이트
    const totalCash = parseInt(cashSnapshot?.total_amount || 0);
    const totalInvestment = parseInt(investmentSnapshot?.total_amount || 0);
    const totalPension = parseInt(pensionSnapshot?.total_amount || 0);
    const totalAll = totalCash + totalInvestment + totalPension;

    $('#total-cash-assets').text('₩' + totalCash.toLocaleString());
    $('#total-investment-assets').text('₩' + totalInvestment.toLocaleString());
    $('#total-pension-assets').text('₩' + totalPension.toLocaleString());
    $('#total-all-assets').text('₩' + totalAll.toLocaleString());

    // 각 자산 섹션에 아카이브 메시지 표시
    displayArchiveMessage('cash-assets-table', 'cash-assets-container', cashSnapshot);
    displayArchiveMessage('investment-assets-table', 'investment-assets-container', investmentSnapshot);
    displayArchiveMessage('pension-assets-table', 'pension-assets-container', pensionSnapshot);
}

function displayArchiveMessage(tableId, containerId, snapshot) {
    const table = $('#' + tableId + ' tbody');
    const container = $('#' + containerId);

    table.empty();
    container.empty();

    if (snapshot) {
        const message = `
            <tr>
                <td colspan="4" class="center-align" style="padding: 20px;">
                    <h6>${assetsCurrentSelectedMonth} 아카이브 데이터</h6>
                    <p>총 ${snapshot.asset_type} 자산: ₩${parseInt(snapshot.total_amount || 0).toLocaleString()}</p>
                    <p>자산 항목 수: ${snapshot.total_count || 0}개</p>
                </td>
            </tr>
        `;
        table.html(message);

        const cardMessage = `
            <div class="center-align" style="padding: 20px;">
                <h6>${assetsCurrentSelectedMonth} 아카이브 데이터</h6>
                <p>총 ${snapshot.asset_type} 자산: ₩${parseInt(snapshot.total_amount || 0).toLocaleString()}</p>
                <p>자산 항목 수: ${snapshot.total_count || 0}개</p>
            </div>
        `;
        container.html(cardMessage);
    } else {
        const message = `${assetsCurrentSelectedMonth}의 ${snapshot?.asset_type || ''} 자산 아카이브 데이터가 없습니다.`;
        table.html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);
        container.html(`<div class="center-align">${message}</div>`);
    }
}

function displayNoAssetsArchiveMessage() {
    const message = `${assetsCurrentSelectedMonth}의 자산 아카이브 데이터가 없습니다.`;

    // 총자산현황 초기화
    $('#total-cash-assets').text('₩0');
    $('#total-investment-assets').text('₩0');
    $('#total-pension-assets').text('₩0');
    $('#total-all-assets').text('₩0');

    // 각 섹션에 메시지 표시
    $('#cash-assets-table tbody').html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);
    $('#investment-assets-table tbody').html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);
    $('#pension-assets-table tbody').html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);

    $('#cash-assets-container').html(`<div class="center-align">${message}</div>`);
    $('#investment-assets-container').html(`<div class="center-align">${message}</div>`);
    $('#pension-assets-container').html(`<div class="center-align">${message}</div>`);
}

function createAssetsMonthlySnapshot() {
    const [year, month] = assetsCurrentSelectedMonth.split('-');

    $.ajax({
        url: 'http://localhost:8080/api/monthly-snapshots/create',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            year: parseInt(year),
            month: parseInt(month)
        }),
        success: function(response) {
            if (response.success) {
                showMessage(`${assetsCurrentSelectedMonth} 자산 스냅샷이 생성되었습니다.`, 'success');
                loadAssetsMonthData(assetsCurrentSelectedMonth);
            } else {
                showMessage('자산 스냅샷 생성에 실패했습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function editAssetsArchiveData() {
    showMessage('자산 아카이브 수정 기능은 개발 중입니다.', 'info');
}

function loadCashAssets() {
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateCashAssetsTable(response.data.data);
                $('#loading').hide();
                $('#dashboard-content').show();
            } else {
                showError('현금 자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            showError('서버와의 연결에 실패했습니다: ' + error);
        }
    });
}

function loadInvestmentAssets() {
    $.ajax({
        url: 'http://localhost:8080/api/investment-assets',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateInvestmentAssetsTable(response.data.data || response.data);
            } else {
                console.error('투자 자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('투자 자산 서버 연결 실패: ' + error);
        }
    });
}

function loadPensionAssets() {
    $.ajax({
        url: 'http://localhost:8080/api/pension-assets',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updatePensionAssetsTable(response.data.data || response.data);
            } else {
                console.error('연금자산 데이터 로드 실패: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('연금자산 서버 연결 실패: ' + error);
        }
    });
}

function updatePensionAssetsTable(assets) {
    let tbody = $('#pension-assets-detail-table');
    let cardsContainer = $('#pension-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    let totalCurrentValue = 0;
    let totalDepositAmount = 0;

    if (!assets || assets.length === 0) {
        tbody.append('<tr><td colspan="6" class="center-align">연금자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">연금자산이 없습니다.</div>');
        return;
    }

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset) {
        let currentValue = parseInt(asset.current_value || 0);
        let depositAmount = parseInt(asset.deposit_amount || 0);
        let returnRate = depositAmount > 0 ? ((currentValue - depositAmount) / depositAmount * 100).toFixed(2) : '0.00';
        let returnClass = parseFloat(returnRate) >= 0 ? 'positive' : 'negative';

        totalCurrentValue += currentValue;
        totalDepositAmount += depositAmount;

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + (asset.type || '연금저축') + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-current-value="' + currentValue + '" ' +
                     'data-deposit-amount="' + depositAmount + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         (asset.type || '연금저축') +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell editable current-value-cell" style="font-weight: bold; cursor: pointer;" ' +
                         'data-asset-id="' + asset.id + '" data-original-value="' + currentValue + '" data-field="current_value">' +
                         '₩' + currentValue.toLocaleString() +
                     '</td>' +
                     '<td class="positive balance-cell editable deposit-amount-cell" style="font-weight: bold; cursor: pointer;" ' +
                         'data-asset-id="' + asset.id + '" data-original-value="' + depositAmount + '" data-field="deposit_amount">' +
                         '₩' + depositAmount.toLocaleString() +
                     '</td>' +
                     '<td class="' + returnClass + '" style="font-weight: bold;">' + returnRate + '%</td>' +
                     '</tr>');
        tbody.append($row);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + (asset.type || '연금저축') + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-current-value="' + currentValue + '" ' +
                      'data-deposit-amount="' + depositAmount + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + (asset.type || '연금저축') + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">평가금액</div>' +
                          '<div class="asset-card-balance balance-cell editable current-value-cell" ' +
                              'data-asset-id="' + asset.id + '" data-original-value="' + currentValue + '" data-field="current_value">' +
                              '₩' + currentValue.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">납입잔액</div>' +
                          '<div class="asset-card-balance balance-cell editable deposit-amount-cell" ' +
                              'data-asset-id="' + asset.id + '" data-original-value="' + depositAmount + '" data-field="deposit_amount">' +
                              '₩' + depositAmount.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">수익률</div>' +
                          '<div class="asset-card-percentage ' + returnClass + '">' + returnRate + '%</div>' +
                      '</div>' +
                      '</div>');
        cardsContainer.append($card);
    });

    // 총합 및 수익률 계산
    let totalReturnRate = totalDepositAmount > 0 ? ((totalCurrentValue - totalDepositAmount) / totalDepositAmount * 100).toFixed(2) : '0.00';
    let totalReturnClass = parseFloat(totalReturnRate) >= 0 ? 'positive' : 'negative';
    let totalProfit = totalCurrentValue - totalDepositAmount;

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 연금자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalCurrentValue.toLocaleString() + '</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalDepositAmount.toLocaleString() + '</td>' +
                   '<td class="' + totalReturnClass + '" style="font-weight: bold;">' + totalReturnRate + '%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #9C27B0; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #9C27B0;">총 연금자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 평가금액</div>' +
                        '<div style="font-weight: bold; color: #9C27B0; font-size: 1.2em;">₩' + totalCurrentValue.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 납입잔액</div>' +
                        '<div style="font-weight: bold; color: #424242;">₩' + totalDepositAmount.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">수익률</div>' +
                        '<div class="asset-card-percentage ' + totalReturnClass + '" style="font-weight: bold;">' + totalReturnRate + '%</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">수익금</div>' +
                        '<div class="' + totalReturnClass + '" style="font-weight: bold;">' + (totalProfit >= 0 ? '+' : '') + '₩' + Math.abs(totalProfit).toLocaleString() + '</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);

    // 편집 이벤트 리스너 업데이트
    setupPensionBalanceEditing();

    // 총자산현황 업데이트
    updateTotalAssets();
}

function updateInvestmentAssetsTable(assets) {
    let tbody = $('#investment-assets-detail-table');
    let cardsContainer = $('#investment-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    // 투자자산 데이터를 전역 변수로 저장 (자산군별 비중 계산용)
    window.investmentAssetsData = assets;

    let totalBalance = 0;

    if (!assets || assets.length === 0) {
        tbody.append('<tr><td colspan="5" class="center-align">저축 + 투자 자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">저축 + 투자 자산이 없습니다.</div>');
        return;
    }

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset) {
        // current_value가 있으면 투자자산, balance가 있으면 저축자산으로 처리
        let assetBalance = parseInt(asset.current_value || asset.balance || 0);
        totalBalance += assetBalance;

        // 구분 매핑: category -> 구분
        let assetType = asset.category || asset.type || '저축';
        if (assetType === '주식' || assetType === 'ETF' || assetType === '펀드' || assetType === '채권' || assetType === '리츠') {
            assetType = asset.category;
        } else if (assetType === '현금') {
            assetType = '저축';
        } else {
            assetType = asset.category || '혼합';
        }

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + assetType + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-balance="' + assetBalance + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         assetType +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell editable" style="font-weight: bold; cursor: pointer;" ' +
                         'data-asset-id="' + asset.id + '" data-original-balance="' + assetBalance + '">' +
                         '₩' + assetBalance.toLocaleString() +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.percentage || 0) + '%</td>' +
                     '</tr>');
        tbody.append($row);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + assetType + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-balance="' + assetBalance + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + assetType + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">잔액</div>' +
                          '<div class="asset-card-balance balance-cell editable" ' +
                              'data-asset-id="' + asset.id + '" data-original-balance="' + assetBalance + '">' +
                              '₩' + assetBalance.toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">비중</div>' +
                          '<div class="asset-card-percentage">' + (asset.percentage || 0) + '%</div>' +
                      '</div>' +
                      '</div>');
        cardsContainer.append($card);
    });

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 저축 + 투자 자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalBalance.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important;">100%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #FF9800; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #FF9800;">총 저축 + 투자 자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 잔액</div>' +
                        '<div style="font-weight: bold; color: #FF9800; font-size: 1.2em;">₩' + totalBalance.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">비중</div>' +
                        '<div class="asset-card-percentage" style="font-weight: bold;">100%</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);

    // 잔액 편집 이벤트 리스너 업데이트
    setupBalanceEditing();

    // 더블클릭/롱프레스 이벤트 리스너 업데이트
    setupRowEditing();

    // 총자산현황 업데이트
    updateTotalAssets();

    // 자산군별 비중 업데이트
    updateAssetAllocation(assets);
}

function updateCashAssetsTable(assets) {
    let tbody = $('#cash-assets-detail-table');
    let cardsContainer = $('#cash-assets-detail-cards');

    tbody.empty();
    cardsContainer.empty();

    // 현금성 자산 데이터를 전역 변수로 저장 (자산군별 비중 계산용)
    window.cashAssetsData = assets;

    let totalBalance = 0;

    if (assets.length === 0) {
        tbody.append('<tr><td colspan="5" class="center-align">현금성 자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">현금성 자산이 없습니다.</div>');
        return;
    }

    // 자산 목록 표시 (테이블과 카드 모두)
    assets.forEach(function(asset) {
        totalBalance += parseInt(asset.balance || 0);

        // 테이블 행 생성 (데스크톱용)
        let $row = $('<tr class="asset-row" data-asset-id="' + asset.id + '" ' +
                     'data-type="' + (asset.type || '현금') + '" ' +
                     'data-account="' + (asset.account_name || '') + '" ' +
                     'data-item-name="' + (asset.item_name || '') + '" ' +
                     'data-balance="' + asset.balance + '">' +
                     '<td style="color: #424242 !important;">' +
                         '<span class="drag-handle" style="display: none;"><i class="material-icons">drag_handle</i></span>' +
                         (asset.type || '현금') +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.account_name || '-') + '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.item_name || '-') + '</td>' +
                     '<td class="positive balance-cell editable" style="font-weight: bold; cursor: pointer;" ' +
                         'data-asset-id="' + asset.id + '" data-original-balance="' + asset.balance + '">' +
                         '₩' + parseInt(asset.balance || 0).toLocaleString() +
                     '</td>' +
                     '<td style="color: #424242 !important;">' + (asset.percentage || 0) + '%</td>' +
                     '</tr>');
        tbody.append($row);

        // 카드 생성 (모바일용)
        let $card = $('<div class="asset-card" data-asset-id="' + asset.id + '" ' +
                      'data-type="' + (asset.type || '현금') + '" ' +
                      'data-account="' + (asset.account_name || '') + '" ' +
                      'data-item-name="' + (asset.item_name || '') + '" ' +
                      'data-balance="' + asset.balance + '">' +
                      '<div class="asset-card-header">' +
                          '<div class="asset-card-title">' + (asset.item_name || '-') + '</div>' +
                          '<div class="asset-card-type">' + (asset.type || '현금') + '</div>' +
                          '<div class="mobile-drag-handle"><i class="material-icons">drag_handle</i></div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">계좌</div>' +
                          '<div class="asset-card-value">' + (asset.account_name || '-') + '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">잔액</div>' +
                          '<div class="asset-card-balance balance-cell editable" ' +
                              'data-asset-id="' + asset.id + '" data-original-balance="' + asset.balance + '">' +
                              '₩' + parseInt(asset.balance || 0).toLocaleString() +
                          '</div>' +
                      '</div>' +
                      '<div class="asset-card-row">' +
                          '<div class="asset-card-label">비중</div>' +
                          '<div class="asset-card-percentage">' + (asset.percentage || 0) + '%</div>' +
                      '</div>' +
                      '</div>');
        cardsContainer.append($card);
    });

    // 잔액 편집 이벤트 리스너 추가
    setupBalanceEditing();

    // 더블클릭/롱프레스 이벤트 리스너 추가
    setupRowEditing();

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td colspan="3" style="color: #424242 !important; text-align: right;">총 현금성 자산:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalBalance.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important;">100%</td>' +
                   '</tr>';
    tbody.append(totalRow);

    // 총합 카드 추가 (모바일만)
    let totalCard = '<div class="asset-card" style="border-left-color: #4CAF50; background-color: #f8f9fa;">' +
                    '<div class="asset-card-header">' +
                        '<div class="asset-card-title" style="color: #4CAF50;">총 현금성 자산</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">총 잔액</div>' +
                        '<div style="font-weight: bold; color: #4CAF50; font-size: 1.2em;">₩' + totalBalance.toLocaleString() + '</div>' +
                    '</div>' +
                    '<div class="asset-card-row">' +
                        '<div class="asset-card-label">비중</div>' +
                        '<div class="asset-card-percentage" style="font-weight: bold;">100%</div>' +
                    '</div>' +
                    '</div>';
    cardsContainer.append(totalCard);

    // 총자산현황 업데이트
    updateTotalAssets();

    // 자산군별 비중 업데이트 (투자자산 데이터가 있을 때만)
    if (window.investmentAssetsData && window.investmentAssetsData.length > 0) {
        updateAssetAllocation(window.investmentAssetsData);
    }
}

function setupBalanceEditing() {
    let currentlyEditing = null;

    // 잔액 셀 클릭 이벤트
    $('.balance-cell.editable').off('click').on('click', function() {
        if (currentlyEditing && currentlyEditing[0] !== this) {
            // 다른 셀이 편집중이면 먼저 처리
            handleEditCancel(currentlyEditing);
        }

        if ($(this).find('input').length > 0) {
            return; // 이미 편집중이면 무시
        }

        startBalanceEdit($(this));
        currentlyEditing = $(this);
    });

    // 다른 곳 클릭 시 편집 완료 확인
    $(document).off('click.balance-edit').on('click.balance-edit', function(e) {
        if (currentlyEditing && !$(e.target).closest('.balance-cell').length) {
            handleEditComplete(currentlyEditing);
            currentlyEditing = null;
        }
    });
}

function startBalanceEdit(cell) {
    let originalBalance = parseInt(cell.data('original-balance'));
    let assetId = cell.data('asset-id');

    // 현재 내용을 input으로 교체
    let input = $('<input type="number" class="balance-input" value="' + originalBalance + '" ' +
                 'style="width: 100%; border: 2px solid #2196F3; padding: 5px; text-align: right; font-weight: bold;" ' +
                 'min="0" step="1000">');

    cell.html(input);
    input.focus().select();

    // Enter 키로 확인
    input.on('keydown', function(e) {
        if (e.key === 'Enter') {
            handleEditComplete(cell);
            $(document).off('click.balance-edit');
        } else if (e.key === 'Escape') {
            handleEditCancel(cell);
            $(document).off('click.balance-edit');
        }
    });
}

function handleEditComplete(cell) {
    let input = cell.find('.balance-input');
    if (input.length === 0) return;

    let newBalance = parseInt(input.val()) || 0;
    let originalBalance = parseInt(cell.data('original-balance'));
    let assetId = cell.data('asset-id');

    if (newBalance !== originalBalance) {
        // 변경사항이 있으면 확인
        if (confirm('잔액을 ₩' + newBalance.toLocaleString() + '(으)로 수정하시겠습니까?')) {
            updateAssetBalance(assetId, newBalance, cell);
        } else {
            // 취소 시 원래 값으로 복원
            restoreOriginalBalance(cell);
        }
    } else {
        // 변경사항 없으면 그냥 복원
        restoreOriginalBalance(cell);
    }
}

function handleEditCancel(cell) {
    restoreOriginalBalance(cell);
}

function restoreOriginalBalance(cell) {
    let originalBalance = parseInt(cell.data('original-balance'));
    cell.html('₩' + originalBalance.toLocaleString());
}

function updateAssetBalance(assetId, newBalance, cell) {
    // 로딩 표시
    cell.html('<i class="material-icons" style="font-size: 18px;">hourglass_empty</i> 수정중...');

    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            balance: newBalance
        }),
        success: function(response) {
            if (response.success) {
                // 성공 시 새로운 값으로 업데이트
                cell.data('original-balance', newBalance);
                cell.html('₩' + newBalance.toLocaleString());

                // 성공 메시지 (짧게 표시)
                showSuccessMessage('잔액이 수정되었습니다.');

                // 전체 테이블 새로고침 (비중 재계산을 위해)
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                showError('수정 실패: ' + response.message);
                restoreOriginalBalance(cell);
            }
        },
        error: function(xhr, status, error) {
            showError('수정 중 오류 발생: ' + error);
            restoreOriginalBalance(cell);
        }
    });
}

function showSuccessMessage(message) {
    // 임시 성공 메시지 표시
    let successAlert = $('<div class="card green white-text" style="position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 10px;">' +
                        '<div class="card-content">' + message + '</div></div>');
    $('body').append(successAlert);

    setTimeout(function() {
        successAlert.fadeOut(500, function() {
            $(this).remove();
        });
    }, 2000);
}

function setupRowEditing() {
    let longPressTimer;
    let isLongPress = false;

    // 테이블 행과 카드 모두에서 이벤트 제거
    $('.asset-row, .asset-card').off('dblclick touchstart touchend touchmove');

    // 데스크톱: 더블클릭 이벤트 (테이블 행)
    $('.asset-row').on('dblclick', function(e) {
        // 잔액 셀은 제외 (인라인 편집 우선)
        if (!$(e.target).hasClass('balance-cell')) {
            e.preventDefault();
            openEditModal($(this));
        }
    });

    // 모바일: 카드 롱프레스 이벤트
    $('.asset-card').on('touchstart', function(e) {
        if ($(e.target).hasClass('balance-cell')) return; // 잔액 셀 제외

        const $card = $(this);
        isLongPress = false;

        // 햅틱 피드백 대신 시각적 피드백
        longPressTimer = setTimeout(function() {
            isLongPress = true;
            $card.addClass('long-press-active');

            // 진동 효과 (지원되는 경우)
            if ('vibrate' in navigator) {
                navigator.vibrate(100);
            }

            openEditModal($card);

            setTimeout(function() {
                $card.removeClass('long-press-active');
            }, 300);
        }, 600); // 600ms 롱프레스
    });

    // 카드 터치 이벤트 정리
    $('.asset-card').on('touchend touchmove', function() {
        clearTimeout(longPressTimer);
    });

    // 일반 터치는 롱프레스가 아닐 때만 처리
    $('.asset-card').on('touchend', function() {
        if (!isLongPress) {
            // 일반 터치 처리 (필요시)
        }
    });
}

function openEditModal($row) {
    const assetId = $row.data('asset-id');
    const account = $row.data('account');
    const itemName = $row.data('item-name');

    // 모달 폼에 데이터 채우기
    $('#edit-account').val(account);
    $('#edit-item-name').val(itemName);

    // 모달에 자산 ID 저장
    $('#edit-modal').data('asset-id', assetId);

    // Materialize 컴포넌트 업데이트
    M.updateTextFields();

    // 모달 열기
    const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
    modal.open();
}

function saveEditedAsset() {
    const assetId = $('#edit-modal').data('asset-id');
    const formData = {
        type: '현금',
        account_name: $('#edit-account').val(),
        item_name: $('#edit-item-name').val()
    };

    // 간단한 클라이언트 검증
    if (!formData.item_name.trim()) {
        M.toast({html: '종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '자산 정보가 수정되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '수정 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '수정 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

function saveNewAsset() {
    const formData = {
        type: '현금',
        account_name: $('#add-account').val(),
        item_name: $('#add-item-name').val(),
        balance: parseInt($('#add-balance').val()) || 0
    };

    // 간단한 클라이언트 검증
    if (!formData.item_name.trim()) {
        M.toast({html: '종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-asset-modal'));
                modal.close();

                // 폼 초기화
                $('#add-form')[0].reset();
                M.updateTextFields();

                // 성공 메시지
                M.toast({html: '새 자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '추가 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

function setupPensionBalanceEditing() {
    let currentlyEditing = null;

    // 편집 가능한 셀 클릭 이벤트
    $('.current-value-cell.editable, .deposit-amount-cell.editable').off('click').on('click', function() {
        if (currentlyEditing && currentlyEditing[0] !== this) {
            // 다른 셀이 편집중이면 먼저 처리
            handlePensionEditCancel(currentlyEditing);
        }

        if ($(this).find('input').length > 0) {
            return; // 이미 편집중이면 무시
        }

        startPensionBalanceEdit($(this));
        currentlyEditing = $(this);
    });

    // 테이블 행과 카드 모두에서 이벤트 제거
    $('#pension-assets-detail-table .asset-row, #pension-assets-detail-cards .asset-card').off('dblclick touchstart touchend touchmove');

    // 데스크톱: 더블클릭 이벤트 (테이블 행)
    $('#pension-assets-detail-table').off('dblclick', '.asset-row').on('dblclick', '.asset-row', function(e) {
        // 잔액 셀은 제외 (인라인 편집 우선)
        if (!$(e.target).hasClass('balance-cell')) {
            e.preventDefault();
            if (currentlyEditing) {
                handlePensionEditComplete(currentlyEditing);
                currentlyEditing = null;
            }
            openPensionEditModal($(this));
        }
    });

    // 모바일: 카드 롱프레스 이벤트
    let pensionLongPressTimer;
    let pensionIsLongPress = false;

    $('#pension-assets-detail-cards').off('touchstart', '.asset-card').on('touchstart', '.asset-card', function(e) {
        if ($(e.target).hasClass('balance-cell')) return; // 잔액 셀 제외

        const $card = $(this);
        pensionIsLongPress = false;

        // 햅틱 피드백 대신 시각적 피드백
        pensionLongPressTimer = setTimeout(function() {
            pensionIsLongPress = true;
            $card.addClass('long-press-active');

            // 진동 효과 (지원되는 경우)
            if ('vibrate' in navigator) {
                navigator.vibrate(100);
            }

            if (currentlyEditing) {
                handlePensionEditComplete(currentlyEditing);
                currentlyEditing = null;
            }
            openPensionEditModal($card);

            setTimeout(function() {
                $card.removeClass('long-press-active');
            }, 300);
        }, 600); // 600ms 롱프레스
    });

    // 카드 터치 이벤트 정리
    $('#pension-assets-detail-cards').off('touchend touchmove', '.asset-card').on('touchend touchmove', '.asset-card', function() {
        clearTimeout(pensionLongPressTimer);
    });

    // 일반 터치는 롱프레스가 아닐 때만 처리
    $('#pension-assets-detail-cards').off('touchend', '.asset-card').on('touchend', '.asset-card', function() {
        if (!pensionIsLongPress) {
            // 일반 터치 처리 (필요시)
        }
    });

    // 다른 곳 클릭 시 편집 완료 확인
    $(document).off('click.pension-balance-edit').on('click.pension-balance-edit', function(e) {
        if (currentlyEditing && !$(e.target).closest('.current-value-cell, .deposit-amount-cell').length) {
            handlePensionEditComplete(currentlyEditing);
            currentlyEditing = null;
        }
    });
}

function startPensionBalanceEdit(cell) {
    let originalValue = parseInt(cell.data('original-value'));
    let assetId = cell.data('asset-id');
    let field = cell.data('field');

    // 현재 내용을 input으로 교체
    let input = $('<input type="number" class="balance-input" value="' + originalValue + '" ' +
                 'style="width: 100%; border: 2px solid #9C27B0; padding: 5px; text-align: right; font-weight: bold;" ' +
                 'min="0" step="1000">');

    cell.html(input);
    input.focus().select();

    // Enter 키로 확인
    input.on('keydown', function(e) {
        if (e.key === 'Enter') {
            handlePensionEditComplete(cell);
            $(document).off('click.pension-balance-edit');
        } else if (e.key === 'Escape') {
            handlePensionEditCancel(cell);
            $(document).off('click.pension-balance-edit');
        }
    });
}

function handlePensionEditComplete(cell) {
    let input = cell.find('.balance-input');
    if (input.length === 0) return;

    let newValue = parseInt(input.val()) || 0;
    let originalValue = parseInt(cell.data('original-value'));
    let assetId = cell.data('asset-id');
    let field = cell.data('field');
    let fieldName = field === 'current_value' ? '평가금액' : '납입잔액';

    if (newValue !== originalValue) {
        // 변경사항이 있으면 확인
        if (confirm(fieldName + '을 ₩' + newValue.toLocaleString() + '(으)로 수정하시겠습니까?')) {
            updatePensionAssetValue(assetId, field, newValue, cell);
        } else {
            // 취소 시 원래 값으로 복원
            restorePensionOriginalValue(cell);
        }
    } else {
        // 변경사항 없으면 그냥 복원
        restorePensionOriginalValue(cell);
    }
}

function handlePensionEditCancel(cell) {
    restorePensionOriginalValue(cell);
}

function restorePensionOriginalValue(cell) {
    let originalValue = parseInt(cell.data('original-value'));
    cell.html('₩' + originalValue.toLocaleString());
}

function updatePensionAssetValue(assetId, field, newValue, cell) {
    // 로딩 표시
    cell.html('<i class="material-icons" style="font-size: 18px;">hourglass_empty</i> 수정중...');

    let updateData = {};
    updateData[field] = newValue;

    $.ajax({
        url: 'http://localhost:8080/api/pension-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(updateData),
        success: function(response) {
            if (response.success) {
                // 성공 시 새로운 값으로 업데이트
                cell.data('original-value', newValue);
                cell.html('₩' + newValue.toLocaleString());

                // 성공 메시지 (짧게 표시)
                showSuccessMessage('연금자산이 수정되었습니다.');

                // 전체 테이블 새로고침 (수익률 재계산을 위해)
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                showError('수정 실패: ' + response.message);
                restorePensionOriginalValue(cell);
            }
        },
        error: function(xhr, status, error) {
            showError('수정 중 오류 발생: ' + error);
            restorePensionOriginalValue(cell);
        }
    });
}

function saveNewPensionAsset() {
    const formData = {
        type: $('#add-pension-type').val(),
        account_name: $('#add-pension-account').val() || '-',
        item_name: $('#add-pension-item-name').val(),
        current_value: parseInt($('#add-pension-current-value').val()) || 0,
        deposit_amount: parseInt($('#add-pension-deposit-amount').val()) || 0
    };

    // 간단한 클라이언트 검증
    if (!formData.type || !formData.item_name.trim()) {
        M.toast({html: '연금유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/pension-assets',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-pension-modal'));
                modal.close();

                // 폼 초기화
                $('#add-pension-form')[0].reset();
                M.updateTextFields();
                M.FormSelect.init(document.getElementById('add-pension-type'));

                // 성공 메시지
                M.toast({html: '새 연금자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '추가 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

function saveNewInvestmentAsset() {
    const currentValue = parseInt($('#add-investment-balance').val()) || 0;
    const formData = {
        category: $('#add-investment-type').val(),
        account_name: $('#add-investment-account').val() || '투자계좌',
        item_name: $('#add-investment-item-name').val(),
        current_value: currentValue,
        deposit_amount: currentValue // 투자원금을 현재가치와 동일하게 설정
    };

    // 간단한 클라이언트 검증
    if (!formData.category || !formData.item_name.trim()) {
        M.toast({html: '투자유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/investment-assets',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('add-investment-modal'));
                modal.close();

                // 폼 초기화
                $('#add-investment-form')[0].reset();
                M.updateTextFields();
                M.FormSelect.init(document.getElementById('add-investment-type'));

                // 성공 메시지
                M.toast({html: '새 투자자산이 추가되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadInvestmentAssets();
                }, 500);
            } else {
                M.toast({html: '추가 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '추가 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

let isReorderMode = false;

function toggleReorderMode() {
    isReorderMode = !isReorderMode;
    const $toggle = $('#reorder-toggle');
    const $tbody = $('#cash-assets-detail-table');
    const $cardsContainer = $('#cash-assets-detail-cards');

    if (isReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('.drag-handle, .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveNewOrder();
            }
        });

        // 카드 sortable 활성화 (모바일)
        $cardsContainer.sortable({
            handle: '.mobile-drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('.balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('.drag-handle, .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('.disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function saveNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#cash-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#cash-assets-detail-cards .asset-card').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    }

    // API 호출하여 순서 저장
    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/reorder',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadCashAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadCashAssets();
        }
    });
}

let isInvestmentReorderMode = false;

function toggleInvestmentReorderMode() {
    isInvestmentReorderMode = !isInvestmentReorderMode;
    const $toggle = $('#investment-reorder-toggle');
    const $tbody = $('#investment-assets-detail-table');
    const $cardsContainer = $('#investment-assets-detail-cards');

    if (isInvestmentReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('#investment-assets-detail-table .drag-handle, #investment-assets-detail-cards .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveInvestmentNewOrder();
            }
        });

        // 카드 sortable 활성화 (모바일)
        $cardsContainer.sortable({
            handle: '.mobile-drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                saveInvestmentNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('#investment-assets-detail-table .balance-cell.editable, #investment-assets-detail-cards .balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('#investment-assets-detail-table .drag-handle, #investment-assets-detail-cards .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('#investment-assets-detail-table .disabled-while-sorting, #investment-assets-detail-cards .disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function saveInvestmentNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#investment-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#investment-assets-detail-cards .asset-card').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    }

    // API 호출하여 순서 저장
    $.ajax({
        url: 'http://localhost:8080/api/investment-assets/reorder',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Investment order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadInvestmentAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadInvestmentAssets();
        }
    });
}

let isPensionReorderMode = false;

function togglePensionReorderMode() {
    isPensionReorderMode = !isPensionReorderMode;
    const $toggle = $('#pension-reorder-toggle');
    const $tbody = $('#pension-assets-detail-table');
    const $cardsContainer = $('#pension-assets-detail-cards');

    if (isPensionReorderMode) {
        // 순서 변경 모드 활성화
        $toggle.removeClass('blue').addClass('orange').html('<i class="material-icons left">check</i>완료');

        // 테이블과 카드 모두에 sortable-enabled 클래스 추가
        $tbody.addClass('sortable-enabled');
        $cardsContainer.addClass('sortable-enabled');

        // 드래그 핸들 표시
        $('#pension-assets-detail-table .drag-handle, #pension-assets-detail-cards .mobile-drag-handle').show();

        // 테이블 sortable 활성화 (데스크톱)
        $tbody.sortable({
            handle: '.drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                savePensionNewOrder();
            }
        });

        // 카드 sortable 활성화 (모바일)
        $cardsContainer.sortable({
            handle: '.mobile-drag-handle',
            helper: 'clone',
            placeholder: 'ui-sortable-placeholder',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                savePensionNewOrder();
            }
        });

        // 편집 기능 비활성화
        $('#pension-assets-detail-table .balance-cell.editable, #pension-assets-detail-cards .balance-cell.editable').removeClass('editable').addClass('disabled-while-sorting');

        M.toast({html: '드래그하여 순서를 변경하세요', classes: 'blue'});
    } else {
        // 일반 모드로 복원
        $toggle.removeClass('orange').addClass('blue').html('<i class="material-icons left">swap_vert</i>순서변경');

        // sortable-enabled 클래스 제거
        $tbody.removeClass('sortable-enabled');
        $cardsContainer.removeClass('sortable-enabled');

        // 드래그 핸들 숨기기
        $('#pension-assets-detail-table .drag-handle, #pension-assets-detail-cards .mobile-drag-handle').hide();

        // jQuery UI sortable 비활성화
        if ($tbody.hasClass('ui-sortable')) {
            $tbody.sortable('destroy');
        }
        if ($cardsContainer.hasClass('ui-sortable')) {
            $cardsContainer.sortable('destroy');
        }

        // 편집 기능 복원
        $('#pension-assets-detail-table .disabled-while-sorting, #pension-assets-detail-cards .disabled-while-sorting').addClass('editable').removeClass('disabled-while-sorting');

        M.toast({html: '순서 변경이 완료되었습니다', classes: 'green'});
    }
}

function savePensionNewOrder() {
    const orders = [];

    // 현재 보이는 컨테이너(데스크톱: 테이블, 모바일: 카드)에서 순서 가져오기
    if ($(window).width() > 768) {
        // 데스크톱: 테이블 행에서 순서 가져오기
        $('#pension-assets-detail-table .asset-row').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    } else {
        // 모바일: 카드에서 순서 가져오기
        $('#pension-assets-detail-cards .asset-card').each(function(index) {
            const assetId = $(this).data('asset-id');
            if (assetId) {
                orders.push({
                    id: parseInt(assetId)
                });
            }
        });
    }

    // API 호출하여 순서 저장
    $.ajax({
        url: 'http://localhost:8080/api/pension-assets/reorder',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({
            orders: orders
        }),
        success: function(response) {
            if (response.success) {
                // 순서가 성공적으로 저장됨
                console.log('Pension order updated successfully');
            } else {
                M.toast({html: '순서 저장 실패: ' + response.message, classes: 'red'});
                // 실패시 테이블 새로고침
                loadPensionAssets();
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '순서 저장 중 오류 발생: ' + error, classes: 'red'});
            // 실패시 테이블 새로고침
            loadPensionAssets();
        }
    });
}

function openPensionEditModal($row) {
    const assetId = $row.data('asset-id');
    const type = $row.data('type');
    const account = $row.data('account');
    const itemName = $row.data('item-name');

    // 모달 폼에 데이터 채우기
    $('#edit-pension-type').val(type);
    $('#edit-pension-account').val(account || '');
    $('#edit-pension-item-name').val(itemName);

    // 모달에 자산 ID 저장
    $('#edit-pension-modal').data('asset-id', assetId);

    // Materialize 컴포넌트 업데이트
    M.updateTextFields();
    M.FormSelect.init(document.getElementById('edit-pension-type'));

    // 모달 열기
    const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
    modal.open();
}

function saveEditedPensionAsset() {
    const assetId = $('#edit-pension-modal').data('asset-id');
    const formData = {
        type: $('#edit-pension-type').val(),
        account_name: $('#edit-pension-account').val() || '',
        item_name: $('#edit-pension-item-name').val()
    };

    // 간단한 클라이언트 검증
    if (!formData.type || !formData.item_name.trim()) {
        M.toast({html: '연금유형과 종목명을 입력해주세요.', classes: 'red'});
        return;
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/pension-assets/' + assetId,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '연금자산 정보가 수정되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                M.toast({html: '수정 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '수정 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 현금성 자산 삭제 함수
function deleteAsset() {
    const assetId = $('#edit-modal').data('asset-id');

    if (!confirm('이 자산을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        return;
    }

    $.ajax({
        url: 'http://localhost:8080/api/cash-assets/' + assetId,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '자산이 삭제되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadCashAssets();
                }, 500);
            } else {
                M.toast({html: '삭제 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '삭제 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 연금자산 삭제 함수
function deletePensionAsset() {
    const assetId = $('#edit-pension-modal').data('asset-id');

    if (!confirm('이 연금자산을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        return;
    }

    $.ajax({
        url: 'http://localhost:8080/api/pension-assets/' + assetId,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                // 모달 닫기
                const modal = M.Modal.getInstance(document.getElementById('edit-pension-modal'));
                modal.close();

                // 성공 메시지
                M.toast({html: '연금자산이 삭제되었습니다.', classes: 'green'});

                // 테이블 새로고침
                setTimeout(function() {
                    loadPensionAssets();
                }, 500);
            } else {
                M.toast({html: '삭제 실패: ' + response.message, classes: 'red'});
            }
        },
        error: function(xhr, status, error) {
            M.toast({html: '삭제 중 오류 발생: ' + error, classes: 'red'});
        }
    });
}

// 총자산현황 업데이트 함수
function updateTotalAssets() {
    let cashTotal = 0;
    let investmentTotal = 0;
    let pensionTotal = 0;

    // 현금성 자산 합계 계산
    $('#cash-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.balance-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        cashTotal += balance;
    });

    // 저축+투자 자산 합계 계산
    $('#investment-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.balance-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        investmentTotal += balance;
    });

    // 연금 자산 합계 계산 (평가금액만)
    $('#pension-assets-detail-table .asset-row').each(function() {
        const balanceText = $(this).find('.current-value-cell').text().replace(/[,원₩]/g, '').trim();
        const balance = parseInt(balanceText) || 0;
        pensionTotal += balance;
    });

    const totalAll = cashTotal + investmentTotal + pensionTotal;

    // UI 업데이트
    $('#total-cash-assets').text(formatCurrency(cashTotal));
    $('#total-investment-assets').text(formatCurrency(investmentTotal));
    $('#total-pension-assets').text(formatCurrency(pensionTotal));
    $('#total-all-assets').text(formatCurrency(totalAll));
}

// 통화 포맷팅 함수
function formatCurrency(amount) {
    if (amount === 0) return '0원';
    return Math.round(amount).toLocaleString() + '원';
}

// 자산군별 비중 업데이트 함수
function updateAssetAllocation(assets) {
    let tbody = $('#asset-allocation-table');
    let cardsContainer = $('#asset-allocation-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!assets || assets.length === 0) {
        tbody.append('<tr><td colspan="3" class="center-align">자산이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">자산이 없습니다.</div>');
        return;
    }

    // 자산군별 합계 계산
    let categoryTotals = {};
    let totalAmount = 0;

    assets.forEach(function(asset) {
        const balance = parseInt(asset.current_value || asset.balance || 0);
        const category = asset.category || asset.type || '기타';

        // 혼합형의 경우 현금(30%), 주식(70%)로 분리
        if (category === '혼합') {
            // 현금 부분 (30%)
            if (!categoryTotals['현금']) {
                categoryTotals['현금'] = 0;
            }
            categoryTotals['현금'] += Math.round(balance * 0.3);

            // 주식 부분 (70%)
            if (!categoryTotals['주식']) {
                categoryTotals['주식'] = 0;
            }
            categoryTotals['주식'] += Math.round(balance * 0.7);
        } else {
            if (!categoryTotals[category]) {
                categoryTotals[category] = 0;
            }
            categoryTotals[category] += balance;
        }

        totalAmount += balance;
    });

    // 현금성 자산도 포함 (전역에서 가져오기) - 모두 현금으로 분류
    if (window.cashAssetsData && window.cashAssetsData.length > 0) {
        window.cashAssetsData.forEach(function(asset) {
            const balance = parseInt(asset.balance || 0);

            // 현금성 자산은 모두 현금 카테고리 (혼합형에서 분리된 현금과 합산)
            if (!categoryTotals['현금']) {
                categoryTotals['현금'] = 0;
            }
            categoryTotals['현금'] += balance;

            totalAmount += balance;
        });
    }

    // 자산군별 비중 테이블 생성
    Object.keys(categoryTotals).sort().forEach(function(category) {
        const amount = categoryTotals[category];
        const percentage = totalAmount > 0 ? ((amount / totalAmount) * 100).toFixed(2) : 0;

        // 테이블 행 추가
        let row = '<tr>' +
                  '<td style="color: #424242 !important;">' + category + '</td>' +
                  '<td class="positive" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important; font-weight: bold;">' + percentage + '%</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="asset-card" style="margin-bottom: 10px;">' +
                   '<div class="asset-card-header">' +
                       '<div class="asset-card-title">' + category + '</div>' +
                   '</div>' +
                   '<div class="asset-card-row">' +
                       '<div class="asset-card-label">잔액</div>' +
                       '<div class="asset-card-balance" style="font-weight: bold;">₩' + amount.toLocaleString() + '</div>' +
                   '</div>' +
                   '<div class="asset-card-row">' +
                       '<div class="asset-card-label">비중</div>' +
                       '<div class="asset-card-percentage" style="font-weight: bold;">' + percentage + '%</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 총합 행 추가 (테이블만)
    let totalRow = '<tr style="background-color: #f5f5f5; font-weight: bold;">' +
                   '<td style="color: #424242 !important; text-align: right;">총계:</td>' +
                   '<td class="positive" style="font-weight: bold;">₩' + totalAmount.toLocaleString() + '</td>' +
                   '<td style="color: #424242 !important; font-weight: bold;">100.00%</td>' +
                   '</tr>';
    tbody.append(totalRow);
}

function showError(message) {
    $('#loading').hide();
    $('#error-message .card-content span.card-title').text('오류 발생');
    $('#error-message .card-content p').text(message);
    $('#error-message').show();
}
</script>

<?php include 'includes/footer.php'; ?>