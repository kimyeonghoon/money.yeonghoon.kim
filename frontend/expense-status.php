<?php
$pageTitle = '지출현황';
include 'includes/header.php';
?>

<main class="container">
    <div class="section">
        <div class="row">
            <div class="col s12">
                <h4 class="section-title"><i class="material-icons left">account_balance_wallet</i>지출현황 관리</h4>
                <p class="section-description">고정지출과 선납지출을 통합 관리하여 월별 지출 계획을 세우고 관리하세요.</p>
            </div>
        </div>

        <!-- 탭 네비게이션 -->
        <div class="row">
            <div class="col s12">
                <ul class="tabs">
                    <li class="tab col s6"><a href="#fixed-expenses" class="active">고정지출</a></li>
                    <li class="tab col s6"><a href="#prepaid-expenses">선납지출</a></li>
                </ul>
            </div>
        </div>

        <!-- 고정지출 탭 -->
        <div id="fixed-expenses" class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">
                        <i class="material-icons left">repeat</i>고정지출 관리
                        <a class="btn-floating btn-small waves-effect waves-light green right modal-trigger" data-target="add-fixed-modal">
                            <i class="material-icons">add</i>
                        </a>
                    </span>
                    <p>매월 정기적으로 발생하는 지출을 관리합니다. (월세, 보험료, 구독료 등)</p>

                    <div class="row">
                        <div class="col s12">
                            <table class="striped responsive-table">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>금액</th>
                                        <th>결제일</th>
                                        <th>카테고리</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody id="fixed-expenses-table">
                                    <tr>
                                        <td colspan="5" class="center-align">고정지출 항목이 없습니다.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 선납지출 탭 -->
        <div id="prepaid-expenses" class="col s12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">
                        <i class="material-icons left">payment</i>선납지출 관리
                        <a class="btn-floating btn-small waves-effect waves-light orange right modal-trigger" data-target="add-prepaid-modal">
                            <i class="material-icons">add</i>
                        </a>
                    </span>
                    <p>연간 또는 장기간 미리 지불한 비용을 관리합니다. (연간 보험료, 멤버십 등)</p>

                    <div class="row">
                        <div class="col s12">
                            <table class="striped responsive-table">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>총 금액</th>
                                        <th>기간</th>
                                        <th>월 분할액</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody id="prepaid-expenses-table">
                                    <tr>
                                        <td colspan="5" class="center-align">선납지출 항목이 없습니다.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 지출 요약 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title"><i class="material-icons left">assessment</i>월별 지출 요약</span>
                        <div class="row">
                            <div class="col s12 m6">
                                <div class="card-panel center-align">
                                    <h5 id="total-fixed-amount">₩0</h5>
                                    <p>월 고정지출</p>
                                </div>
                            </div>
                            <div class="col s12 m6">
                                <div class="card-panel center-align">
                                    <h5 id="total-monthly-amount">₩0</h5>
                                    <p>월 분할 선납액</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col s12">
                                <div class="card-panel center-align blue lighten-4">
                                    <h4 id="total-monthly-expenses">₩0</h4>
                                    <p><strong>총 월별 예상 지출</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 고정지출 추가 모달 -->
<div id="add-fixed-modal" class="modal">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>고정지출 추가</h4>
        <form id="add-fixed-form">
            <div class="row">
                <div class="input-field col s12 m6">
                    <input id="fixed-item-name" type="text" class="validate" required>
                    <label for="fixed-item-name">항목명 *</label>
                </div>
                <div class="input-field col s12 m6">
                    <input id="fixed-amount" type="number" class="validate" required>
                    <label for="fixed-amount">금액 *</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6">
                    <input id="fixed-payment-date" type="number" min="1" max="31" class="validate">
                    <label for="fixed-payment-date">결제일 (1-31)</label>
                </div>
                <div class="input-field col s12 m6">
                    <input id="fixed-category" type="text" class="validate">
                    <label for="fixed-category">카테고리</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn green" id="save-fixed">저장</a>
    </div>
</div>

<!-- 선납지출 추가 모달 -->
<div id="add-prepaid-modal" class="modal">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>선납지출 추가</h4>
        <form id="add-prepaid-form">
            <div class="row">
                <div class="input-field col s12 m6">
                    <input id="prepaid-item-name" type="text" class="validate" required>
                    <label for="prepaid-item-name">항목명 *</label>
                </div>
                <div class="input-field col s12 m6">
                    <input id="prepaid-total-amount" type="number" class="validate" required>
                    <label for="prepaid-total-amount">총 금액 *</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6">
                    <input id="prepaid-start-date" type="date" class="validate" required>
                    <label for="prepaid-start-date">시작일 *</label>
                </div>
                <div class="input-field col s12 m6">
                    <input id="prepaid-end-date" type="date" class="validate" required>
                    <label for="prepaid-end-date">종료일 *</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn orange" id="save-prepaid">저장</a>
    </div>
</div>

<script>
$(document).ready(function(){
    // 모달 초기화
    M.Modal.init(document.querySelectorAll('.modal'));

    // 탭 초기화
    M.Tabs.init(document.querySelectorAll('.tabs'));

    // 데이터 로드
    loadFixedExpenses();
    loadPrepaidExpenses();
    updateSummary();
});

function loadFixedExpenses() {
    // TODO: API 연동
    console.log('Loading fixed expenses...');
}

function loadPrepaidExpenses() {
    // TODO: API 연동
    console.log('Loading prepaid expenses...');
}

function updateSummary() {
    // TODO: 총계 계산 및 업데이트
    console.log('Updating summary...');
}
</script>

<?php include 'includes/footer.php'; ?>