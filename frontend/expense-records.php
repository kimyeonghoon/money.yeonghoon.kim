<?php
$pageTitle = '일간지출내역';
include 'includes/header.php';
?>

<style>
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
</style>

<main class="container">
    <div class="section">
        <div class="row">
            <div class="col s12">
                <h4 class="section-title"><i class="material-icons left">receipt</i>일간지출내역</h4>
            </div>
        </div>

        <!-- 일간 지출현황 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <h5 class="section-title center-align" style="margin-bottom: 20px;">📊 일간 지출현황</h5>
                        <div class="row">
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #FF5722; margin: 0;">오늘 지출</h6>
                                    <span id="today-expenses-total" style="font-size: 20px; font-weight: bold; color: #FF5722;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #2196F3; margin: 0;">이번 주</h6>
                                    <span id="week-expenses-total" style="font-size: 20px; font-weight: bold; color: #2196F3;">₩0</span>
                                </div>
                            </div>
                            <div class="col s12 m4">
                                <div class="center-align">
                                    <h6 style="color: #9C27B0; margin: 0;">이번 달</h6>
                                    <span id="month-expenses-total" style="font-size: 20px; font-weight: bold; color: #9C27B0;">₩0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 일간지출 기록 섹션 -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <div class="section-header">
                            <h5 class="section-title">📝 일간지출 기록</h5>
                            <div class="section-header-actions">
                                <button id="add-expense-btn" class="btn-floating waves-effect waves-light green">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>

                        <!-- 데스크톱용 테이블 -->
                        <div class="responsive-table hide-on-small-only">
                            <table class="striped">
                                <thead>
                                    <tr>
                                        <th>날짜</th>
                                        <th>총 지출</th>
                                        <th>식비</th>
                                        <th>생필품</th>
                                        <th>교통비</th>
                                        <th>기타</th>
                                    </tr>
                                </thead>
                                <tbody id="daily-expenses-table">
                                    <tr>
                                        <td colspan="6" class="center-align">데이터를 불러오는 중...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 모바일용 카드 -->
                        <div class="hide-on-med-and-up" id="daily-expenses-cards">
                            <div class="center-align">데이터를 불러오는 중...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- 일간지출 수정 모달 -->
<div id="edit-daily-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">edit</i>일간지출 편집</h4>
        <div class="row">
            <form id="edit-daily-expense-form" class="col s12">
                <input type="hidden" id="edit-daily-expense-id">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="edit-expense-date" type="date" class="validate" readonly style="background-color: #f5f5f5;">
                        <label for="edit-expense-date">지출일 (자동생성)</label>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="edit-total-amount" type="number" class="validate" readonly style="background-color: #f5f5f5;">
                        <label for="edit-total-amount">총 지출금액 (자동계산)</label>
                        <span class="helper-text">카테고리별 금액의 합계</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m3">
                        <input id="edit-food-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-food-cost">식비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-necessities-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-necessities-cost">생필품비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-transportation-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-transportation-cost">교통비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m3">
                        <input id="edit-other-cost" type="number" class="validate" min="0" max="999999999">
                        <label for="edit-other-cost">기타</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn orange" id="save-daily-expense-edit">
            <i class="material-icons left">save</i>저장
        </a>
    </div>
</div>

<!-- 지출 추가 모달 -->
<div id="add-expense-modal" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4><i class="material-icons left">add</i>오늘 지출 추가</h4>
        <p style="color: #666; margin-bottom: 20px;">오늘 사용한 금액을 카테고리별로 입력하세요. 기존 금액에 추가됩니다.</p>
        <div class="row">
            <form id="add-expense-form" class="col s12">
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="add-food-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-food-cost">🍽️ 식비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="add-necessities-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-necessities-cost">🛒 생활비</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 m6">
                        <input id="add-transportation-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-transportation-cost">🚌 교통비</label>
                        <span class="helper-text">원</span>
                    </div>
                    <div class="input-field col s12 m6">
                        <input id="add-other-cost" type="number" class="validate" min="0" max="999999999" value="0">
                        <label for="add-other-cost">💰 기타</label>
                        <span class="helper-text">원</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <div class="card blue lighten-5" style="padding: 15px;">
                            <h6 style="margin: 0 0 10px 0; color: #1976D2;">📋 추가할 총액</h6>
                            <span id="add-total-preview" style="font-size: 18px; font-weight: bold; color: #1976D2;">₩0</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">취소</a>
        <a href="#!" class="waves-effect waves-light btn orange" id="save-add-expense">
            <i class="material-icons left">add</i>지출 추가
        </a>
    </div>
</div>

<script>
$(document).ready(function() {
    // 모달 초기화
    M.Modal.init(document.querySelectorAll('.modal'));

    loadDailyExpenses();
    updateExpenseStatistics();
    ensureMissingExpensesExist(); // 누락된 날짜들의 지출 기록 생성

    // 카테고리별 금액 입력시 총액 자동 계산
    $('#edit-food-cost, #edit-necessities-cost, #edit-transportation-cost, #edit-other-cost').on('input', function() {
        calculateEditTotalAmount();
    });

    // 지출 추가 버튼 이벤트 핸들러
    $('#add-expense-btn').on('click', function() {
        openAddExpenseModal();
    });

    // 지출 추가 저장 버튼 이벤트 핸들러
    $('#save-add-expense').on('click', function() {
        saveAddedExpense();
    });

    // 지출 추가 입력시 총액 미리보기
    $('#add-food-cost, #add-necessities-cost, #add-transportation-cost, #add-other-cost').on('input', function() {
        updateAddTotalPreview();
    });

    // 일간지출 수정 버튼 이벤트 핸들러
    $('#save-daily-expense-edit').on('click', function() {
        saveEditedDailyExpense();
    });


    // 테이블 행 더블클릭 이벤트
    $(document).on('dblclick', '.daily-expense-row', function() {
        const expenseId = $(this).data('id');
        openEditDailyExpenseModal(expenseId);
    });

    // 모바일 카드 길게 터치 이벤트
    let touchTimer;
    $(document).on('touchstart', '.daily-expense-card', function(e) {
        const expenseId = $(this).data('id');
        touchTimer = setTimeout(function() {
            openEditDailyExpenseModal(expenseId);
        }, 800); // 800ms 길게 터치
    });

    $(document).on('touchend touchmove', '.daily-expense-card', function() {
        clearTimeout(touchTimer);
    });

    // 모바일 카드 더블 탭 이벤트 (대안)
    $(document).on('dblclick', '.daily-expense-card', function() {
        const expenseId = $(this).data('id');
        openEditDailyExpenseModal(expenseId);
    });
});

function loadDailyExpenses() {
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayDailyExpenses(response.data);
            } else {
                showMessage('일간지출 데이터를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function displayDailyExpenses(expenses) {
    let tbody = $('#daily-expenses-table');
    let cardsContainer = $('#daily-expenses-cards');

    tbody.empty();
    cardsContainer.empty();

    if (!expenses || expenses.length === 0) {
        tbody.append('<tr><td colspan="6" class="center-align">일간지출 기록이 없습니다.</td></tr>');
        cardsContainer.append('<div class="center-align">일간지출 기록이 없습니다.</div>');
        return;
    }

    // 날짜 기준으로 내림차순 정렬 (최신순)
    expenses.sort((a, b) => new Date(b.expense_date) - new Date(a.expense_date));

    expenses.forEach(function(expense) {
        const expenseDate = new Date(expense.expense_date).toLocaleDateString('ko-KR');
        const totalAmount = parseInt(expense.total_amount || 0);
        const foodCost = parseInt(expense.food_cost || 0);
        const necessitiesCost = parseInt(expense.necessities_cost || 0);
        const transportationCost = parseInt(expense.transportation_cost || 0);
        const otherCost = parseInt(expense.other_cost || 0);

        // 테이블 행 추가
        let row = '<tr class="daily-expense-row" data-id="' + expense.id + '" style="cursor: pointer;">' +
                  '<td style="color: #424242 !important;">' + expenseDate + '</td>' +
                  '<td class="negative" style="font-weight: bold;">₩' + totalAmount.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + foodCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + necessitiesCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + transportationCost.toLocaleString() + '</td>' +
                  '<td style="color: #424242 !important;">₩' + otherCost.toLocaleString() + '</td>' +
                  '</tr>';
        tbody.append(row);

        // 모바일 카드 추가
        let card = '<div class="daily-expense-card" data-id="' + expense.id + '" style="margin-bottom: 10px; border-left: 4px solid #FF5722; cursor: pointer;">' +
                   '<div class="card-content" style="padding: 12px;">' +
                       '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">' +
                           '<span style="font-weight: bold; color: #424242;">' + expenseDate + '</span>' +
                           '<span style="font-weight: bold; color: #FF5722;">₩' + totalAmount.toLocaleString() + '</span>' +
                       '</div>' +
                       '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-size: 14px; color: #666;">' +
                           '<span>🍽️ ₩' + foodCost.toLocaleString() + '</span>' +
                           '<span>🛒 ₩' + necessitiesCost.toLocaleString() + '</span>' +
                           '<span>🚌 ₩' + transportationCost.toLocaleString() + '</span>' +
                           '<span>💰 ₩' + otherCost.toLocaleString() + '</span>' +
                       '</div>' +
                   '</div>' +
                   '</div>';
        cardsContainer.append(card);
    });
}

function ensureMissingExpensesExist() {
    // 기존 데이터를 먼저 로드하여 누락된 날짜를 확인
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const existingDates = response.data.map(expense => expense.expense_date);
                const today = new Date();
                const missingDates = [];

                // 지난 7일간 확인
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(today.getDate() - i);
                    const dateStr = date.toISOString().split('T')[0];

                    if (!existingDates.includes(dateStr)) {
                        missingDates.push(dateStr);
                    }
                }

                if (missingDates.length > 0) {
                    createMissingExpenses(missingDates);
                } else {
                    console.log('누락된 지출 기록이 없습니다.');
                }
            }
        },
        error: function() {
            console.log('기존 지출 기록 조회 실패');
        }
    });
}

function createMissingExpenses(dates) {
    let createdCount = 0;
    let processedCount = 0;

    dates.forEach(function(date) {
        $.ajax({
            url: 'http://localhost:8080/api/daily-expenses',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                expense_date: date,
                total_amount: 0,
                food_cost: 0,
                necessities_cost: 0,
                transportation_cost: 0,
                other_cost: 0
            }),
            success: function(response) {
                processedCount++;
                if (response.success) {
                    createdCount++;
                    console.log(`${date} 지출 기록 생성 완료`);
                }

                if (processedCount === dates.length) {
                    finalizeMissingExpensesCreation(createdCount);
                }
            },
            error: function(xhr) {
                processedCount++;
                console.log(`${date} 지출 기록 생성 실패:`, xhr.responseJSON);

                if (processedCount === dates.length) {
                    finalizeMissingExpensesCreation(createdCount);
                }
            }
        });
    });
}

function finalizeMissingExpensesCreation(createdCount) {
    if (createdCount > 0) {
        console.log(`누락된 지출 기록 ${createdCount}개 생성 완료`);
        showMessage(`누락된 ${createdCount}개 날짜의 지출 기록을 생성했습니다.`, 'info');
    }

    // 생성 후 데이터 다시 로드
    setTimeout(function() {
        loadDailyExpenses();
    }, 500);
}

function calculateEditTotalAmount() {
    const foodCost = parseInt($('#edit-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#edit-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#edit-transportation-cost').val() || 0);
    const otherCost = parseInt($('#edit-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;
    $('#edit-total-amount').val(totalAmount);

    // 라벨 업데이트
    M.updateTextFields();
}

function updateExpenseStatistics() {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];

    // 이번 주 시작일 계산 (월요일)
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + 1);
    const weekStartStr = startOfWeek.toISOString().split('T')[0];

    // 이번 달 시작일
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const monthStartStr = startOfMonth.toISOString().split('T')[0];

    // 통계 API 호출
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses/statistics',
        type: 'GET',
        data: {
            today: todayStr,
            week_start: weekStartStr,
            month_start: monthStartStr
        },
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#today-expenses-total').text('₩' + (stats.today || 0).toLocaleString());
                $('#week-expenses-total').text('₩' + (stats.week || 0).toLocaleString());
                $('#month-expenses-total').text('₩' + (stats.month || 0).toLocaleString());
            }
        },
        error: function() {
            // 통계 로드 실패시 기본값 유지
            console.log('통계 데이터 로드 실패');
        }
    });
}


function openEditDailyExpenseModal(expenseId) {
    // API에서 일간지출 정보 가져오기
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses/' + expenseId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const expense = response.data;

                // 폼에 데이터 채우기
                $('#edit-daily-expense-id').val(expense.id);
                $('#edit-expense-date').val(expense.expense_date);
                $('#edit-total-amount').val(expense.total_amount);
                $('#edit-food-cost').val(expense.food_cost || '');
                $('#edit-necessities-cost').val(expense.necessities_cost || '');
                $('#edit-transportation-cost').val(expense.transportation_cost || '');
                $('#edit-other-cost').val(expense.other_cost || '');

                // 라벨 업데이트
                M.updateTextFields();

                // 총액 자동 계산
                calculateEditTotalAmount();

                // 모달 열기
                M.Modal.getInstance(document.getElementById('edit-daily-expense-modal')).open();
            } else {
                showMessage('일간지출 정보를 불러올 수 없습니다.', 'error');
            }
        },
        error: function() {
            showMessage('서버 연결에 실패했습니다.', 'error');
        }
    });
}

function saveEditedDailyExpense() {
    const expenseId = $('#edit-daily-expense-id').val();
    const expenseDate = $('#edit-expense-date').val();
    const totalAmount = $('#edit-total-amount').val();
    const foodCost = $('#edit-food-cost').val() || 0;
    const necessitiesCost = $('#edit-necessities-cost').val() || 0;
    const transportationCost = $('#edit-transportation-cost').val() || 0;
    const otherCost = $('#edit-other-cost').val() || 0;

    // 유효성 검사 (날짜는 자동생성이므로 검사 불필요)

    // API 요청 데이터 준비
    const data = {
        expense_date: expenseDate,
        total_amount: parseInt(totalAmount),
        food_cost: parseInt(foodCost),
        necessities_cost: parseInt(necessitiesCost),
        transportation_cost: parseInt(transportationCost),
        other_cost: parseInt(otherCost)
    };

    // API 호출
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses/' + expenseId,
        type: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage('일간지출이 저장되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('edit-daily-expense-modal')).close();

                setTimeout(function() {
                    loadDailyExpenses();
                    updateExpenseStatistics();
                }, 500);
            } else {
                showMessage(response.message || '일간지출 저장에 실패했습니다.', 'error');
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

function openAddExpenseModal() {
    // 폼 초기화
    $('#add-food-cost').val(0);
    $('#add-necessities-cost').val(0);
    $('#add-transportation-cost').val(0);
    $('#add-other-cost').val(0);

    // 라벨 업데이트
    M.updateTextFields();

    // 총액 미리보기 업데이트
    updateAddTotalPreview();

    // 모달 열기
    M.Modal.getInstance(document.getElementById('add-expense-modal')).open();
}

function updateAddTotalPreview() {
    const foodCost = parseInt($('#add-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#add-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#add-transportation-cost').val() || 0);
    const otherCost = parseInt($('#add-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;
    $('#add-total-preview').text('₩' + totalAmount.toLocaleString());
}

function saveAddedExpense() {
    const foodCost = parseInt($('#add-food-cost').val() || 0);
    const necessitiesCost = parseInt($('#add-necessities-cost').val() || 0);
    const transportationCost = parseInt($('#add-transportation-cost').val() || 0);
    const otherCost = parseInt($('#add-other-cost').val() || 0);

    const totalAmount = foodCost + necessitiesCost + transportationCost + otherCost;

    if (totalAmount <= 0) {
        showMessage('추가할 금액을 입력해주세요.', 'error');
        return;
    }

    const today = new Date().toISOString().split('T')[0];

    // 오늘 지출 추가 API 호출
    $.ajax({
        url: 'http://localhost:8080/api/daily-expenses/add-today',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            expense_date: today,
            food_cost: foodCost,
            necessities_cost: necessitiesCost,
            transportation_cost: transportationCost,
            other_cost: otherCost
        }),
        success: function(response) {
            if (response.success) {
                showMessage('지출이 추가되었습니다.', 'success');
                M.Modal.getInstance(document.getElementById('add-expense-modal')).close();

                // 데이터 새로고침
                setTimeout(function() {
                    loadDailyExpenses();
                    updateExpenseStatistics();
                }, 500);
            } else {
                showMessage(response.message || '지출 추가에 실패했습니다.', 'error');
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


function showMessage(text, type) {
    let colorClass = 'blue';
    if (type === 'error') colorClass = 'red';
    if (type === 'success') colorClass = 'green';
    if (type === 'info') colorClass = 'blue';

    M.toast({
        html: text,
        classes: colorClass + ' white-text',
        displayLength: 4000
    });
}
</script>

<?php include 'includes/footer.php'; ?>