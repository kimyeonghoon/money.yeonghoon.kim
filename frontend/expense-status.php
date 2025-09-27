<?php
$pageTitle = '지출현황';
include 'includes/header.php';
?>

<main class="container">
    <div class="section">
        <div class="row">
            <div class="col s12">
                <h4 class="section-title"><i class="material-icons left">account_balance_wallet</i>지출현황</h4>
            </div>
        </div>

        <!-- 월별 선택기 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content center-align">
                        <h5 style="margin-bottom: 15px;">📅 조회 기간</h5>
                        <div class="row">
                            <div class="col s12 m6 offset-m3">
                                <div class="input-field">
                                    <select id="month-selector">
                                        <!-- 동적으로 생성됨 -->
                                    </select>
                                    <label>조회 월 선택</label>
                                </div>
                            </div>
                        </div>
                        <div id="archive-controls" style="display: none; margin-top: 10px;">
                            <button class="btn blue" id="edit-archive-btn">
                                <i class="material-icons left">edit</i>아카이브 수정
                            </button>
                            <button class="btn green" id="create-archive-btn" style="margin-left: 10px;">
                                <i class="material-icons left">archive</i>스냅샷 생성
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 월간 지출현황 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <h5 class="section-title center-align" style="margin-bottom: 20px;">💰 월간 지출현황</h5>
                        <div class="row">
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #f44336; margin: 0;">고정지출(예정)</h6>
                                    <span id="fixed-expenses-total" style="font-size: 20px; font-weight: bold; color: #f44336;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #2196F3; margin: 0;">고정지출(선납)</h6>
                                    <span id="prepaid-expenses-total" style="font-size: 20px; font-weight: bold; color: #2196F3;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #FF5722; margin: 0;">총 월간지출</h6>
                                    <span id="total-monthly-expenses" style="font-size: 20px; font-weight: bold; color: #FF5722;">₩0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 고정지출(예정) 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h5 class="section-title" style="margin: 0;">📋 고정지출(예정)</h5>
                            <button class="btn-floating waves-effect waves-light green modal-trigger"
                                    data-target="add-fixed-expense-modal" title="고정지출 추가" id="add-fixed-expense-btn">
                                <i class="material-icons">add</i>
                            </button>
                        </div>

                        <!-- 데스크톱용 테이블 -->
                        <div class="responsive-table hide-on-small-only">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>금액</th>
                                        <th>결제일</th>
                                        <th>수단</th>
                                    </tr>
                                </thead>
                                <tbody id="fixed-expenses-table">
                                    <tr>
                                        <td colspan="4" class="center-align">데이터를 불러오는 중...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 모바일용 카드 -->
                        <div class="hide-on-med-and-up" id="fixed-expenses-cards">
                            <div class="center-align">데이터를 불러오는 중...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 고정지출(선납) 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h5 class="section-title" style="margin: 0;">💳 고정지출(선납)</h5>
                            <button class="btn-floating waves-effect waves-light blue modal-trigger"
                                    data-target="add-prepaid-expense-modal" title="선납지출 추가" id="add-prepaid-expense-btn">
                                <i class="material-icons">add</i>
                            </button>
                        </div>

                        <!-- 데스크톱용 테이블 -->
                        <div class="responsive-table hide-on-small-only">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>항목명</th>
                                        <th>금액</th>
                                        <th>결제일</th>
                                        <th>수단</th>
                                    </tr>
                                </thead>
                                <tbody id="prepaid-expenses-table">
                                    <tr>
                                        <td colspan="4" class="center-align">데이터를 불러오는 중...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 모바일용 카드 -->
                        <div class="hide-on-med-and-up" id="prepaid-expenses-cards">
                            <div class="center-align">데이터를 불러오는 중...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 고정지출 추가 모달 -->
<div id="add-fixed-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>고정지출 추가</h4>
        <div class="row">
            <form id="add-fixed-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="add-fixed-item-name" type="text" class="validate" required maxlength="100">
                        <label for="add-fixed-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="add-fixed-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="add-fixed-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="add-fixed-payment-date" type="number" class="validate" min="1" max="31">
                        <label for="add-fixed-payment-date">결제일</label>
                        <span class="helper-text">1-31일 (선택사항)</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="add-fixed-payment-method">
                            <option value="" disabled selected>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn green" id="save-fixed-expense-add">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 선납지출 추가 모달 -->
<div id="add-prepaid-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>선납지출 추가</h4>
        <div class="row">
            <form id="add-prepaid-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="add-prepaid-item-name" type="text" class="validate" required maxlength="100">
                        <label for="add-prepaid-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="add-prepaid-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="add-prepaid-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="add-prepaid-payment-date" type="number" class="validate" required min="1" max="31">
                        <label for="add-prepaid-payment-date">결제일*</label>
                        <span class="helper-text" data-error="결제일을 입력해주세요" data-success="">1-31일</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="add-prepaid-payment-method" required>
                            <option value="" disabled selected>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn blue" id="save-prepaid-expense-add">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 고정지출 수정 모달 -->
<div id="edit-fixed-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>고정지출 수정</h4>
        <div class="row">
            <form id="edit-fixed-expense-form" class="col s12">
                <input type="hidden" id="edit-fixed-expense-id">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="edit-fixed-item-name" type="text" class="validate" required maxlength="100">
                        <label for="edit-fixed-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="edit-fixed-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="edit-fixed-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="edit-fixed-payment-date" type="number" class="validate" min="1" max="31">
                        <label for="edit-fixed-payment-date">결제일</label>
                        <span class="helper-text">1-31일 (선택사항)</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="edit-fixed-payment-method">
                            <option value="" disabled>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="waves-effect waves-light btn red" id="delete-fixed-expense-confirm" style="float: left;">
            <i class="material-icons left">delete</i>삭제
        </a>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn green" id="save-fixed-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 선납지출 수정 모달 -->
<div id="edit-prepaid-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>선납지출 수정</h4>
        <div class="row">
            <form id="edit-prepaid-expense-form" class="col s12">
                <input type="hidden" id="edit-prepaid-expense-id">
                <div class="row">
                    <div class="input-field col s12">
                        <input id="edit-prepaid-item-name" type="text" class="validate" required maxlength="100">
                        <label for="edit-prepaid-item-name">항목명*</label>
                        <span class="helper-text" data-error="항목명을 입력해주세요" data-success="">최대 100자</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m4">
                        <input id="edit-prepaid-amount" type="number" class="validate" required min="0" max="999999999">
                        <label for="edit-prepaid-amount">금액*</label>
                        <span class="helper-text" data-error="올바른 금액을 입력해주세요" data-success="">원</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <input id="edit-prepaid-payment-date" type="number" class="validate" required min="1" max="31">
                        <label for="edit-prepaid-payment-date">결제일*</label>
                        <span class="helper-text" data-error="결제일을 입력해주세요" data-success="">1-31일</span>
                    </div>
                    <div class="input-field col s12 m4">
                        <select id="edit-prepaid-payment-method" required>
                            <option value="" disabled>결제수단 선택</option>
                            <option value="현금">현금</option>
                            <option value="체크">체크</option>
                            <option value="신용">신용</option>
                        </select>
                        <label>결제수단*</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="waves-effect waves-light btn red" id="delete-prepaid-expense-confirm" style="float: left;">
            <i class="material-icons left">delete</i>삭제
        </a>
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn blue" id="save-prepaid-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.querySelectorAll('.modal'));

    // 셀렉트 박스 초기화
    M.FormSelect.init(document.querySelectorAll('select'));

    // 월별 선택기 초기화
    initMonthSelector();

    // 현재 월 데이터 로드
    loadCurrentMonthData();

    // 고정지출 추가 버튼 이벤트 핸들러
    $('#save-fixed-expense-add').on('click', function() {
        saveNewFixedExpense();
    });

    // 고정지출 수정 버튼 이벤트 핸들러
    $('#save-fixed-expense-edit').on('click', function() {
        saveEditedFixedExpense();
    });

    // 고정지출 삭제 버튼 이벤트 핸들러
    $('#delete-fixed-expense-confirm').on('click', function() {
        deleteFixedExpense();
    });

    // 선납지출 추가 버튼 이벤트 핸들러
    $('#save-prepaid-expense-add').on('click', function() {
        saveNewPrepaidExpense();
    });

    // 선납지출 수정 버튼 이벤트 핸들러
    $('#save-prepaid-expense-edit').on('click', function() {
        saveEditedPrepaidExpense();
    });

    // 선납지출 삭제 버튼 이벤트 핸들러
    $('#delete-prepaid-expense-confirm').on('click', function() {
        deletePrepaidExpense();
    });

    // 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.expense-row', function() {
        const expenseId = $(this).data('id');
        openEditExpenseModal(expenseId);
    });

    // 모바일 카드 길게 터치 이벤트
    let touchTimer;
    $(document).on('touchstart', '.expense-card', function(e) {
        const expenseId = $(this).data('id');
        touchTimer = setTimeout(function() {
            openEditExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.expense-card', function() {
        clearTimeout(touchTimer);
    });

    // 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.expense-card', function() {
        const expenseId = $(this).data('id');
        openEditExpenseModal(expenseId);
    });

    // 선납지출 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.prepaid-expense-row', function() {
        const expenseId = $(this).data('id');
        openEditPrepaidExpenseModal(expenseId);
    });

    // 선납지출 모바일 카드 길게 터치 이벤트
    let prepaidTouchTimer;
    $(document).on('touchstart', '.prepaid-expense-card', function(e) {
        const expenseId = $(this).data('id');
        prepaidTouchTimer = setTimeout(function() {
            openEditPrepaidExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.prepaid-expense-card', function() {
        clearTimeout(prepaidTouchTimer);
    });

    // 선납지출 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.prepaid-expense-card', function() {
        const expenseId = $(this).data('id');
        openEditPrepaidExpenseModal(expenseId);
    });

    // 월별 선택기 변경 이벤트
    $('#month-selector').on('change', function() {
        const selectedMonth = $(this).val();
        loadMonthData(selectedMonth);
    });

    // 아카이브 수정 버튼
    $('#edit-archive-btn').on('click', function() {
        editArchiveData();
    });

    // 스냅샷 생성 버튼
    $('#create-archive-btn').on('click', function() {
        createMonthlySnapshot();
    });
});

let currentViewMode = 'current'; // 'current' or 'archive'
let currentSelectedMonth = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0');

function initMonthSelector() {
    const monthSelector = $('#month-selector');
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

function loadCurrentMonthData() {
    currentViewMode = 'current';
    $('#archive-controls').hide();
    $('#add-fixed-expense-btn, #add-prepaid-expense-btn').show();
    loadFixedExpenses();
    loadPrepaidExpenses();
}

function loadMonthData(selectedMonth) {
    currentSelectedMonth = selectedMonth;
    const currentYearMonth = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0');

    if (selectedMonth === currentYearMonth) {
        // 현재 월 - 실시간 데이터
        currentViewMode = 'current';
        $('#archive-controls').hide();
        $('#add-fixed-expense-btn, #add-prepaid-expense-btn').show();
        loadFixedExpenses();
        loadPrepaidExpenses();
    } else {
        // 과거 월 - 아카이브 데이터
        currentViewMode = 'archive';
        $('#archive-controls').show();
        $('#add-fixed-expense-btn, #add-prepaid-expense-btn').hide();
        loadArchiveData(selectedMonth);
    }
}

function loadArchiveData(month) {
    const [year, monthNum] = month.split('-');

    // 아카이브된 지출 요약 데이터 로드
    $.ajax({
        url: `http://localhost:8080/api/monthly-snapshots/expenses?year=${year}&month=${parseInt(monthNum)}`,
        type: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                displayArchiveExpenseData(response.data);
            } else {
                displayNoArchiveMessage();
            }
        },
        error: function() {
            displayNoArchiveMessage();
        }
    });
}

function displayArchiveExpenseData(summary) {
    // 고정지출과 선납지출을 아카이브 데이터로 표시 (읽기 전용)
    let fixedTbody = $('#fixed-expenses-table');
    let prepaidTbody = $('#prepaid-expenses-table');
    let fixedCards = $('#fixed-expenses-cards');
    let prepaidCards = $('#prepaid-expenses-cards');

    fixedTbody.empty();
    prepaidTbody.empty();
    fixedCards.empty();
    prepaidCards.empty();

    // 아카이브 요약 정보 표시
    fixedTbody.append(`
        <tr>
            <td colspan="4" class="center-align" style="padding: 20px;">
                <h6>${currentSelectedMonth} 아카이브 데이터</h6>
                <p>총 지출: ₩${parseInt(summary.total_expenses || 0).toLocaleString()}</p>
                <p>지출 일수: ${summary.total_days || 0}일</p>
                <p>일평균: ₩${parseInt(summary.avg_daily_expense || 0).toLocaleString()}</p>
            </td>
        </tr>
    `);

    prepaidTbody.append(`
        <tr>
            <td colspan="4" class="center-align" style="padding: 20px;">
                <p>카테고리별 지출</p>
                <p>식비: ₩${parseInt(summary.food_total || 0).toLocaleString()}</p>
                <p>생필품: ₩${parseInt(summary.necessities_total || 0).toLocaleString()}</p>
                <p>교통비: ₩${parseInt(summary.transportation_total || 0).toLocaleString()}</p>
                <p>기타: ₩${parseInt(summary.other_total || 0).toLocaleString()}</p>
            </td>
        </tr>
    `);

    fixedCards.html(`
        <div class="center-align" style="padding: 20px;">
            <h6>${currentSelectedMonth} 아카이브 데이터</h6>
            <p>총 지출: ₩${parseInt(summary.total_expenses || 0).toLocaleString()}</p>
            <p>지출 일수: ${summary.total_days || 0}일</p>
            <p>일평균: ₩${parseInt(summary.avg_daily_expense || 0).toLocaleString()}</p>
        </div>
    `);

    prepaidCards.html(`
        <div class="center-align" style="padding: 20px;">
            <p><strong>카테고리별 지출</strong></p>
            <p>식비: ₩${parseInt(summary.food_total || 0).toLocaleString()}</p>
            <p>생필품: ₩${parseInt(summary.necessities_total || 0).toLocaleString()}</p>
            <p>교통비: ₩${parseInt(summary.transportation_total || 0).toLocaleString()}</p>
            <p>기타: ₩${parseInt(summary.other_total || 0).toLocaleString()}</p>
        </div>
    `);

    // 총액 업데이트
    $('#fixed-expenses-total').text('₩' + parseInt(summary.total_expenses || 0).toLocaleString());
    $('#prepaid-expenses-total').text('₩0');
    $('#total-monthly-expenses').text('₩' + parseInt(summary.total_expenses || 0).toLocaleString());
}

function displayNoArchiveMessage() {
    let fixedTbody = $('#fixed-expenses-table');
    let prepaidTbody = $('#prepaid-expenses-table');
    let fixedCards = $('#fixed-expenses-cards');
    let prepaidCards = $('#prepaid-expenses-cards');

    const message = `${currentSelectedMonth}의 아카이브 데이터가 없습니다.`;

    fixedTbody.html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);
    prepaidTbody.html(`<tr><td colspan="4" class="center-align">${message}</td></tr>`);
    fixedCards.html(`<div class="center-align">${message}</div>`);
    prepaidCards.html(`<div class="center-align">${message}</div>`);

    $('#fixed-expenses-total').text('₩0');
    $('#prepaid-expenses-total').text('₩0');
    $('#total-monthly-expenses').text('₩0');
}

function createMonthlySnapshot() {
    const [year, month] = currentSelectedMonth.split('-');

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
                showMessage(`${currentSelectedMonth} 스냅샷이 생성되었습니다.`, 'success');
                loadMonthData(currentSelectedMonth);
            } else {
                showMessage('스냅샷 생성에 실패했습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function editArchiveData() {
    showMessage('아카이브 수정 기능은 개발 중입니다.', 'info');
}

function loadFixedExpenses() {
    $.ajax({
        url: 'http://localhost:8080/api/fixed-expenses',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayFixedExpenses(response.data);
            } else {
                showMessage('고정지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayFixedExpenses(expenses) {
    let tbody = $('#fixed-expenses-table');
    let cardsContainer = $('#fixed-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    let totalAmount = 0;

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="4" class="center-align">고정지출이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">고정지출이 없습니다.</div>');
        $('#fixed-expenses-total').text('총 ₩0');
        return;
    }

    // 결제일 기준으로 정렬 (NULL은 미정으로 맨 뒤에)
    expenses.sort((a, b) => {
        const dateA = a.payment_date;
        const dateB = b.payment_date;
        if (dateA === null || dateA === undefined) return 1; // A가 미정이면 뒤로
        if (dateB === null || dateB === undefined) return -1; // B가 미정이면 뒤로
        return dateA - dateB; // 일반 날짜는 오름차순
    });

    expenses.forEach(function(expense) {
        totalAmount += parseInt(expense.amount || 0);

        const paymentDate = expense.payment_date ? expense.payment_date + '일' : '-';
        const amount = parseInt(expense.amount || 0);

        // 테이블 행 추가
        let row = '<tr class="expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #f44336; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span style="font-weight: bold; color: #f44336;">₩' + amount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: flex; justify-content: space-between; font-size: 14px; color: #666;">' +
                           '<span>📅 ' + paymentDate + '</span>' +
                           '<span>💳 ' + (expense.payment_method || '-') + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 총액 업데이트
    $('#fixed-expenses-total').text('₩' + totalAmount.toLocaleString());
    updateMonthlyExpensesTotal();
}

function saveNewFixedExpense() {
    // 폼 데이터 수집
    const itemName = $('#add-fixed-item-name').val().trim();
    const amount = $('#add-fixed-amount').val();
    const paymentDate = $('#add-fixed-payment-date').val();
    const paymentMethod = $('#add-fixed-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#add-fixed-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#add-fixed-amount').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_method: paymentMethod
    };

    // 결제일이 입력된 경우에만 추가
    if (paymentDate && paymentDate >= 1 && paymentDate <= 31) {
        data.payment_date = parseInt(paymentDate);
    }
    // 빈 값이면 payment_date 필드를 포함하지 않음 (NULL로 처리됨)

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/fixed-expenses',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                // 성공 메시지
                showMessage('새 고정지출이 추가되었습니다.', 'success');

                // 모달 닫기
                M.Modal.getInstance(document.getElementById('add-fixed-expense-modal')).close();

                // 폼 초기화
                clearFixedExpenseForm();

                // 테이블 새로고침
                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 추가에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function clearFixedExpenseForm() {
    $('#add-fixed-expense-form')[0].reset();
    M.FormSelect.init(document.querySelectorAll('select'));
    M.updateTextFields();
}

function openEditExpenseModal(expenseId) {
    // API에서 고정지출 정보 가져오기
    $.ajax({
        url: 'http://localhost:8080/api/fixed-expenses/' + expenseId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-fixed-expense-id').val(expense.id);
                $('#edit-fixed-item-name').val(expense.item_name);
                $('#edit-fixed-amount').val(expense.amount);
                $('#edit-fixed-payment-date').val(expense.payment_date || '');
                $('#edit-fixed-payment-method').val(expense.payment_method);

                // 라벨 업데이트
                M.updateTextFields();
                M.FormSelect.init(document.querySelectorAll('select'));

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).open();
            } else {
                showMessage('고정지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedFixedExpense() {
    const expenseId = $('#edit-fixed-expense-id').val();
    const itemName = $('#edit-fixed-item-name').val().trim();
    const amount = $('#edit-fixed-amount').val();
    const paymentDate = $('#edit-fixed-payment-date').val();
    const paymentMethod = $('#edit-fixed-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#edit-fixed-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#edit-fixed-amount').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_method: paymentMethod
    };

    // 결제일 처리 (빈 값이면 null, 값이 있으면 정수)
    if (paymentDate && paymentDate >= 1 && paymentDate <= 31) {
        data.payment_date = parseInt(paymentDate);
    } else {
        data.payment_date = null; // 빈 값일 때 명시적으로 null 전송
    }

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/fixed-expenses/' + expenseId,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('고정지출이 수정되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).close();

                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 수정에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}


function deleteFixedExpense() {
    const expenseId = $('#edit-fixed-expense-id').val();

    if (!expenseId) {
        showMessage('삭제할 고정지출을 선택해주세요.', 'error');
        return;
    }

    if (!confirm('정말로 이 고정지출을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: 'http://localhost:8080/api/fixed-expenses/' + expenseId,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('고정지출이 삭제되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-fixed-expense-modal')).close();

                setTimeout(function() {
                    loadFixedExpenses();
                }, 500);
            } else {
                showMessage(response.message || '고정지출 삭제에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function loadPrepaidExpenses() {
    $.ajax({
        url: 'http://localhost:8080/api/prepaid-expenses',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayPrepaidExpenses(response.data);
            } else {
                showMessage('선납지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayPrepaidExpenses(expenses) {
    let tbody = $('#prepaid-expenses-table');
    let cardsContainer = $('#prepaid-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="4" class="center-align">선납지출이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">선납지출이 없습니다.</div>');
        return;
    }

    // 결제일 기준으로 정렬
    expenses.sort((a, b) => {
        return a.payment_date - b.payment_date;
    });

    expenses.forEach(function(expense) {
        const paymentDate = expense.payment_date + '일';
        const amount = parseInt(expense.amount || 0);

        // 테이블 행 추가
        let row = '<tr class="prepaid-expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + (expense.item_name || '-') + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + amount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">' + paymentDate + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="prepaid-expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #2196F3; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + (expense.item_name || '-') + '</span>' +
                           '<span style="font-weight: bold; color: #2196F3;">₩' + amount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: flex; justify-content: space-between; font-size: 14px; color: #666;">' +
                           '<span>📅 ' + paymentDate + '</span>' +
                           '<span>💳 ' + (expense.payment_method || '-') + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });

    // 선납지출 총액 업데이트
    let totalAmount = expenses.reduce((sum, expense) => sum + parseInt(expense.amount || 0), 0);
    $('#prepaid-expenses-total').text('₩' + totalAmount.toLocaleString());
    updateMonthlyExpensesTotal();
}

function saveNewPrepaidExpense() {
    // 폼 데이터 수집
    const itemName = $('#add-prepaid-item-name').val().trim();
    const amount = $('#add-prepaid-amount').val();
    const paymentDate = $('#add-prepaid-payment-date').val();
    const paymentMethod = $('#add-prepaid-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#add-prepaid-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#add-prepaid-amount').focus();
        return;
    }

    if (!paymentDate || paymentDate < 1 || paymentDate > 31) {
        showMessage('올바른 결제일을 입력해주세요.', 'error');
        $('#add-prepaid-payment-date').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_date: parseInt(paymentDate),
        payment_method: paymentMethod
    };

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/prepaid-expenses',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('새 선납지출이 추가되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('add-prepaid-expense-modal')).close();
                clearPrepaidExpenseForm();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 추가에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function openEditPrepaidExpenseModal(expenseId) {
    // API에서 선납지출 정보 가져오기
    $.ajax({
        url: 'http://localhost:8080/api/prepaid-expenses/' + expenseId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-prepaid-expense-id').val(expense.id);
                $('#edit-prepaid-item-name').val(expense.item_name);
                $('#edit-prepaid-amount').val(expense.amount);
                $('#edit-prepaid-payment-date').val(expense.payment_date);
                $('#edit-prepaid-payment-method').val(expense.payment_method);

                // 라벨 업데이트
                M.updateTextFields();
                M.FormSelect.init(document.querySelectorAll('select'));

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).open();
            } else {
                showMessage('선납지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedPrepaidExpense() {
    const expenseId = $('#edit-prepaid-expense-id').val();
    const itemName = $('#edit-prepaid-item-name').val().trim();
    const amount = $('#edit-prepaid-amount').val();
    const paymentDate = $('#edit-prepaid-payment-date').val();
    const paymentMethod = $('#edit-prepaid-payment-method').val();

    // 유효성 검사
    if (!itemName) {
        showMessage('항목명을 입력해주세요.', 'error');
        $('#edit-prepaid-item-name').focus();
        return;
    }

    if (!amount || amount <= 0) {
        showMessage('올바른 금액을 입력해주세요.', 'error');
        $('#edit-prepaid-amount').focus();
        return;
    }

    if (!paymentDate || paymentDate < 1 || paymentDate > 31) {
        showMessage('올바른 결제일을 입력해주세요.', 'error');
        $('#edit-prepaid-payment-date').focus();
        return;
    }

    if (!paymentMethod) {
        showMessage('결제수단을 선택해주세요.', 'error');
        return;
    }

    // API 요청 데이터 준비
    const data = {
        item_name: itemName,
        amount: parseInt(amount),
        payment_date: parseInt(paymentDate),
        payment_method: paymentMethod
    };

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/prepaid-expenses/' + expenseId,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('선납지출이 수정되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).close();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 수정에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function deletePrepaidExpense() {
    const expenseId = $('#edit-prepaid-expense-id').val();

    if (!expenseId) {
        showMessage('삭제할 선납지출을 선택해주세요.', 'error');
        return;
    }

    if (!confirm('정말로 이 선납지출을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: 'http://localhost:8080/api/prepaid-expenses/' + expenseId,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('선납지출이 삭제되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-prepaid-expense-modal')).close();

                setTimeout(function() {
                    loadPrepaidExpenses();
                }, 500);
            } else {
                showMessage(response.message || '선납지출 삭제에 실패했습니다.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = '서버 연결에 실패했습니다.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showMessage(errorMessage, 'error');
        }
    });
}

function clearPrepaidExpenseForm() {
    $('#add-prepaid-expense-form')[0].reset();
    M.FormSelect.init(document.querySelectorAll('select'));
    M.updateTextFields();
}

function updateMonthlyExpensesTotal() {
    // 고정지출과 선납지출 총액 계산
    const fixedExpensesText = $('#fixed-expenses-total').text();
    const prepaidExpensesText = $('#prepaid-expenses-total').text();

    // 텍스트에서 숫자 추출 (₩ 및 쉼표 제거)
    const fixedAmount = parseInt(fixedExpensesText.replace(/[₩,]/g, '') || '0');
    const prepaidAmount = parseInt(prepaidExpensesText.replace(/[₩,]/g, '') || '0');

    const totalAmount = fixedAmount + prepaidAmount;
    $('#total-monthly-expenses').text('₩' + totalAmount.toLocaleString());
}

function showMessage(text, type) {
    let colorClass = 'blue';
    if (type === 'error') colorClass = 'red';
    if (type === 'success') colorClass = 'green';

    M.toast({
        html: text,
        classes: colorClass + ' white-text',
        displayLength: 3000
    });
}
</script>

<?php include 'includes/footer.php'; ?>