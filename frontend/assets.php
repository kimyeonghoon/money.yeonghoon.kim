<?php
/**
 * 자산현황 페이지 - 머니매니저 시스템
 *
 * 사용자의 모든 자산(현금, 투자, 연금)을 통합적으로 관리하고 조회할 수 있는 메인 대시보드입니다.
 * 실시간 데이터와 아카이브된 과거 데이터를 모두 지원하며, 드래그 앤 드롭으로 순서 변경이 가능합니다.
 *
 * 주요 기능:
 * - 현금성 자산 관리 (은행 계좌, 현금 등)
 * - 투자 자산 관리 (주식, 펀드, ISA 등)
 * - 연금 자산 관리 (연금저축, 퇴직연금 등)
 * - 월별 아카이브 조회
 * - 실시간 자산 총액 및 비율 계산
 * - 모바일 친화적 반응형 UI
 *
 * @package MoneyManager
 * @version 1.0
 * @author YeongHoon Kim
 */

// 페이지 타이틀 설정
$pageTitle = '자산현황';

// 공통 헤더 포함 (인증 확인, 네비게이션, 메타 태그 등)
include 'includes/header.php';
?>

<!-- 자산현황 페이지 전용 스타일시트 -->
<link rel="stylesheet" href="css/assets.css">

    <main class="container">
        <!-- 월별 아카이브 선택기 -->
        <div class="section">
            <div class="card">
                <div class="card-content">
                    <div class="row month-selector-row" style="margin-bottom: 0;">
                        <div class="col s12 m6">
                            <h6 class="month-selector-title" style="margin: 8px 0;"><i class="material-icons left">date_range</i>조회 기간</h6>
                        </div>
                        <div class="col s12 m6">
                            <div class="month-selector-controls input-field" style="margin-top: 0;">
                                <select id="month-selector" class="browser-default">
                                    <option value="current" selected>현재 (실시간)</option>
                                    <!-- 아카이브 월 목록은 JavaScript로 동적 로드 -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="archive-mode-notice" class="card-panel orange lighten-4" style="display:none; margin: 10px 0 0 0; padding: 10px;">
                        <i class="material-icons left" style="margin-right: 8px;">archive</i>
                        <span id="archive-notice-text">과거 데이터 조회 중 - 수정 시 아카이브가 업데이트됩니다</span>
                        <button id="create-snapshot-btn" class="btn-small blue right" style="margin-top: -4px; display: none;">
                            현재 데이터로 스냅샷 생성
                        </button>
                    </div>
                </div>
            </div>
        </div>

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

    <!-- 투자자산 편집 모달 -->
    <div id="edit-investment-modal" class="modal modal-fixed-footer">
        <div class="modal-content">
            <h4><i class="material-icons left">edit</i>투자자산 편집</h4>
            <div class="row">
                <form id="edit-investment-form" class="col s12">
                    <div class="row">
                        <div class="input-field col s12 m6">
                            <select id="edit-investment-type">
                                <option value="저축">💰 저축</option>
                                <option value="주식">📈 주식</option>
                                <option value="ETF">📊 ETF</option>
                                <option value="펀드">🏦 펀드</option>
                                <option value="채권">📋 채권</option>
                                <option value="리츠">🏢 리츠</option>
                                <option value="혼합">🔀 혼합</option>
                            </select>
                            <label>투자유형 *</label>
                        </div>
                        <div class="input-field col s12 m6">
                            <input id="edit-investment-account" type="text" maxlength="100">
                            <label for="edit-investment-account">계좌명</label>
                            <span class="helper-text">예: KB증권, 미래에셋 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-investment-item-name" type="text" maxlength="200" required>
                            <label for="edit-investment-item-name">종목명 *</label>
                            <span class="helper-text">예: KODEX 나스닥100, 삼성전자 등</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="edit-investment-balance" type="number" min="0" step="1000" required>
                            <label for="edit-investment-balance">현재가치 *</label>
                            <span class="helper-text">현재 평가금액 (원 단위)</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer">
            <button id="delete-investment-asset" class="btn waves-effect waves-light red left">
                <i class="material-icons left">delete</i>삭제
            </button>
            <button class="modal-close waves-effect waves-light btn-flat">취소</button>
            <button id="save-investment-edit" class="btn waves-effect waves-light purple">
                <i class="material-icons left">save</i>저장
            </button>
        </div>
    </div>

<script src="js/assets.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>