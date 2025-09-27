<?php
$pageTitle = '변동지출기록';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">receipt</i>일별지출 관리</h4>
                </div>
            </div>

            <!-- 추가/수정 폼 -->
            <div id="expense-form" class="card" style="display: none;">
                <div class="card-content">
                    <span class="card-title" id="form-title">지출 기록</span>
                    <form>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input id="expense-date" type="date" class="validate" required>
                                <label for="expense-date">지출일자*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="total-amount" type="number" readonly>
                                <label for="total-amount">총 금액 (자동 계산)</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input id="food-cost" type="number" value="0">
                                <label for="food-cost">식비 (원)</label>
                                <span class="helper-text">🍽️</span>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="necessities-cost" type="number" value="0">
                                <label for="necessities-cost">생필품비 (원)</label>
                                <span class="helper-text">🛒</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <input id="transportation-cost" type="number" value="0">
                                <label for="transportation-cost">교통비 (원)</label>
                                <span class="helper-text">🚌</span>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="other-cost" type="number" value="0">
                                <label for="other-cost">기타 비용 (원)</label>
                                <span class="helper-text">💸</span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-action">
                    <button id="save-btn" class="btn waves-effect waves-light">
                        <i class="material-icons left">save</i>저장
                    </button>
                    <button id="cancel-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">cancel</i>취소
                    </button>
                </div>
            </div>

            <!-- 필터 및 액션 버튼 -->
            <div class="row">
                <div class="col s12">
                    <div class="card grey lighten-5">
                        <div class="card-content">
                            <div class="row valign-wrapper">
                                <div class="col s12 m4">
                                    <button id="add-expense-btn" class="btn waves-effect waves-light">
                                        <i class="material-icons left">add</i>지출 기록
                                    </button>
                                    <button id="refresh-btn" class="btn grey waves-effect waves-light">
                                        <i class="material-icons left">refresh</i>새로고침
                                    </button>
                                </div>
                                <div class="col s12 m8">
                                    <div class="row valign-wrapper" style="margin-bottom: 0;">
                                        <div class="col s12 m2">
                                            <span>📅 조회기간:</span>
                                        </div>
                                        <div class="col s12 m4">
                                            <input type="date" id="date-from" class="browser-default">
                                        </div>
                                        <div class="col s12 m1 center-align">
                                            <span>~</span>
                                        </div>
                                        <div class="col s12 m4">
                                            <input type="date" id="date-to" class="browser-default">
                                        </div>
                                        <div class="col s12 m1">
                                            <button id="filter-btn" class="btn-small waves-effect waves-light">
                                                <i class="material-icons">search</i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 지출 통계 -->
            <div class="row">
                <div class="col s12 m6 l3">
                    <div class="stats-card red white-text">
                        <div class="stats-icon">
                            <i class="material-icons">today</i>
                        </div>
                        <div class="stats-number" id="today-total">-</div>
                        <div class="stats-label">오늘 지출</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card orange white-text">
                        <div class="stats-icon">
                            <i class="material-icons">date_range</i>
                        </div>
                        <div class="stats-number" id="week-total">-</div>
                        <div class="stats-label">이번주 지출</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card blue white-text">
                        <div class="stats-icon">
                            <i class="material-icons">calendar_month</i>
                        </div>
                        <div class="stats-number" id="month-total">-</div>
                        <div class="stats-label">이번달 지출</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card purple white-text">
                        <div class="stats-icon">
                            <i class="material-icons">trending_down</i>
                        </div>
                        <div class="stats-number" id="daily-average">-</div>
                        <div class="stats-label">평균 일지출</div>
                    </div>
                </div>
            </div>

            <!-- 지출 목록 테이블 -->
            <div class="row">
                <div class="col s12">
                    <div id="loading" class="center-align">
                        <div class="preloader-wrapper active">
                            <div class="spinner-layer spinner-red-only">
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
                        <p>지출 내역을 불러오는 중...</p>
                    </div>

                    <div class="card" id="expenses-table-card" style="display: none;">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>일자</th>
                                            <th>항목명</th>
                                            <th>카테고리</th>
                                            <th>금액</th>
                                            <th>결제수단</th>
                                            <th>비고</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expenses-table-body">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div id="no-data" class="card grey lighten-4" style="display: none;">
                        <div class="card-content center-align">
                            <span class="card-title grey-text">
                                <i class="material-icons large">receipt</i>
                            </span>
                            <p class="grey-text">조회된 지출 내역이 없습니다.</p>
                            <p class="grey-text">새 지출을 기록해보세요! 📝</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 페이징 -->
            <div class="row">
                <div class="col s12 center-align">
                    <div id="pagination">
                    </div>
                </div>
            </div>

            <!-- 메시지 영역 -->
            <div id="message" class="card" style="display: none;">
                <div class="card-content">
                    <span id="message-text"></span>
                </div>
            </div>
        </div>
    </main>

<script>
let editingExpenseId = null;
let currentPage = 1;
const pageSize = 20;

$(document).ready(function() {
    // 오늘 날짜 설정
    const today = new Date().toISOString().split('T')[0];
    $('#expense-date').val(today);

    // 이번달 필터 설정
    const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    const lastDay = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0).toISOString().split('T')[0];
    $('#date-from').val(firstDay);
    $('#date-to').val(lastDay);

    loadExpenses();
    loadStatistics();

    // 이벤트 핸들러
    $('#add-expense-btn').click(showAddForm);
    $('#cancel-btn').click(hideForm);
    $('#save-btn').click(saveExpense);
    $('#refresh-btn').click(function() {
        loadExpenses();
        loadStatistics();
    });
    $('#filter-btn').click(function() {
        currentPage = 1;
        loadExpenses();
    });
});

function loadExpenses() {
    $('#loading').show();
    $('#expenses-table-card').hide();
    $('#no-data').hide();

    let params = {
        page: currentPage,
        limit: pageSize
    };

    // 날짜 필터 적용
    const dateFrom = $('#date-from').val();
    const dateTo = $('#date-to').val();
    if (dateFrom) params.date_from = dateFrom;
    if (dateTo) params.date_to = dateTo;

    $.ajax({
        url: '/api/daily-expenses',
        method: 'GET',
        data: params,
        success: function(response) {
            $('#loading').hide();
            if (response.success) {
                if (response.data.length === 0) {
                    $('#no-data').show();
                } else {
                    displayExpenses(response.data);
                    displayMobileCards(response.data);
                    updatePagination(response.pagination);
                    $('#expenses-table-card').show();
                    $('#expenses-cards-container').show();
                }
            } else {
                showMessage('데이터 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            $('#loading').hide();
            showMessage('서버와의 연결에 실패했습니다.', 'error');
        }
    });
}

function loadStatistics() {
    const today = new Date().toISOString().split('T')[0];
    const thisWeekStart = new Date();
    thisWeekStart.setDate(thisWeekStart.getDate() - thisWeekStart.getDay());
    const weekStart = thisWeekStart.toISOString().split('T')[0];

    const thisMonthStart = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];

    // 오늘 지출
    $.ajax({
        url: '/api/daily-expenses',
        data: { date_from: today, date_to: today },
        success: function(response) {
            if (response.success) {
                const todayTotal = response.data.reduce((sum, exp) => sum + parseInt(exp.total_amount || 0), 0);
                $('#today-total').text(formatMoney(todayTotal));
            }
        }
    });

    // 이번주 지출
    $.ajax({
        url: '/api/daily-expenses',
        data: { date_from: weekStart, date_to: today },
        success: function(response) {
            if (response.success) {
                const weekTotal = response.data.reduce((sum, exp) => sum + parseInt(exp.total_amount || 0), 0);
                $('#week-total').text(formatMoney(weekTotal));
            }
        }
    });

    // 이번달 지출
    $.ajax({
        url: '/api/daily-expenses',
        data: { date_from: thisMonthStart, date_to: today },
        success: function(response) {
            if (response.success) {
                const monthTotal = response.data.reduce((sum, exp) => sum + parseInt(exp.total_amount || 0), 0);
                const daysInMonth = new Date().getDate();
                const dailyAvg = Math.floor(monthTotal / daysInMonth);

                $('#month-total').text(formatMoney(monthTotal));
                $('#daily-average').text(formatMoney(dailyAvg));
            }
        }
    });
}

function displayExpenses(expenses) {
    let tbody = $('#expenses-table-body');
    tbody.empty();

    expenses.forEach(function(expense) {
        let row = '<tr>' +
                  '<td style="color: #424242 !important;">' + expense.expense_date + '</td>' +
                  '<td style="color: #424242 !important;">' + expense.item_name + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.category || '-') + '</td>' +
                  '<td style="font-weight: bold; color: #cc0000 !important;">-' + formatMoney(expense.amount) + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.payment_method || '-') + '</td>' +
                  '<td style="color: #424242 !important;">' + (expense.notes || '-') + '</td>' +
                  '<td>' +
                  '<button onclick="editExpense(' + expense.id + ')" class="btn-small waves-effect waves-light blue" style="margin-right: 5px;"><i class="material-icons left">edit</i>수정</button>' +
                  '<button onclick="deleteExpense(' + expense.id + ')" class="btn-small waves-effect waves-light red"><i class="material-icons left">delete</i>삭제</button>' +
                  '</td>' +
                  '</tr>';
        tbody.append(row);
    });
}

function displayMobileCards(expenses) {
    let container = $('#expenses-cards-container');
    container.empty();

    expenses.forEach(function(expense) {
        let categoryIcon = getDailyCategoryIcon(expense.category);
        let card = $(`
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div class="mobile-card-title">
                        <i class="material-icons mobile-card-icon">${categoryIcon}</i>
                        ${expense.item_name || '-'}
                    </div>
                    <span style="color: #D32F2F; font-weight: bold;">-${formatMoney(expense.amount)}</span>
                </div>
                <div class="mobile-card-meta">
                    <span><strong>${expense.category || '-'}</strong> | ${expense.payment_method || '-'}</span>
                    <span>${expense.expense_date}</span>
                </div>
                <div class="mobile-card-meta">
                    <span>📝 ${expense.notes || '메모 없음'}</span>
                </div>
                <div class="mobile-card-actions">
                    <button onclick="editExpense(${expense.id})" class="btn-small waves-effect waves-light blue">
                        <i class="material-icons left">edit</i>수정
                    </button>
                    <button onclick="deleteExpense(${expense.id})" class="btn-small waves-effect waves-light red">
                        <i class="material-icons left">delete</i>삭제
                    </button>
                </div>
            </div>
        `);

        container.append(card);
    });
}

function getDailyCategoryIcon(category) {
    const iconMap = {
        '식비': 'restaurant',
        '생필품': 'shopping_cart',
        '교통비': 'directions_bus',
        '문화생활': 'movie',
        '의료비': 'local_hospital',
        '기타': 'receipt'
    };
    return iconMap[category] || 'receipt';
}

function updatePagination(pagination) {
    if (!pagination || pagination.pages <= 1) {
        $('#pagination').empty();
        return;
    }

    let paginationHtml = '';

    // 이전 페이지
    if (pagination.page > 1) {
        paginationHtml += '<button class="btn waves-effect waves-light" onclick="changePage(' + (pagination.page - 1) + ')"><i class="material-icons left">chevron_left</i>이전</button>';
    }

    // 페이지 번호들
    const startPage = Math.max(1, pagination.page - 2);
    const endPage = Math.min(pagination.pages, pagination.page + 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.page) {
            paginationHtml += '<button class="btn blue disabled">' + i + '</button>';
        } else {
            paginationHtml += '<button class="btn waves-effect waves-light" onclick="changePage(' + i + ')">' + i + '</button>';
        }
    }

    // 다음 페이지
    if (pagination.page < pagination.pages) {
        paginationHtml += '<button class="btn waves-effect waves-light" onclick="changePage(' + (pagination.page + 1) + ')">다음<i class="material-icons right">chevron_right</i></button>';
    }

    $('#pagination').html(paginationHtml);
}

function changePage(page) {
    currentPage = page;
    loadExpenses();
}

function showAddForm() {
    editingExpenseId = null;
    $('#form-title').text('지출 기록');
    clearForm();
    $('#expense-form').show();
    $('#expense-date').focus();
}

function editExpense(id) {
    editingExpenseId = id;
    $('#form-title').text('지출 수정');

    $.ajax({
        url: '/api/daily-expenses/' + id,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                fillForm(response.data);
                $('#expense-form').show();
            } else {
                showMessage('지출 정보 로드 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('지출 정보를 불러올 수 없습니다.', 'error');
        }
    });
}

function fillForm(expense) {
    $('#expense-date').val(expense.expense_date);
    $('#item-name').val(expense.item_name);
    $('#amount').val(expense.amount);
    $('#category').val(expense.category || '기타');
    $('#payment-method').val(expense.payment_method || '카드');
    $('#notes').val(expense.notes || '');
}

function clearForm() {
    const today = new Date().toISOString().split('T')[0];
    $('#expense-date').val(today);
    $('#item-name').val('');
    $('#amount').val('');
    $('#category').val('식비');
    $('#payment-method').val('카드');
    $('#notes').val('');
}

function hideForm() {
    $('#expense-form').hide();
    editingExpenseId = null;
}

function saveExpense() {
    let data = {
        expense_date: $('#expense-date').val(),
        item_name: $('#item-name').val().trim(),
        amount: parseInt($('#amount').val()) || 0,
        category: $('#category').val(),
        payment_method: $('#payment-method').val(),
        notes: $('#notes').val().trim()
    };

    // 유효성 검사
    if (!data.expense_date || !data.item_name || data.amount <= 0) {
        showMessage('필수 항목을 모두 입력해주세요.', 'error');
        return;
    }

    let url = '/api/daily-expenses';
    let method = 'POST';

    if (editingExpenseId) {
        url += '/' + editingExpenseId;
        method = 'PUT';
    }

    $.ajax({
        url: url,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                showMessage(editingExpenseId ? '지출이 수정되었습니다.' : '지출이 기록되었습니다.', 'success');
                hideForm();
                loadExpenses();
                loadStatistics();
            } else {
                showMessage('저장 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function deleteExpense(id) {
    if (!confirm('이 지출 기록을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '/api/daily-expenses/' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('지출 기록이 삭제되었습니다.', 'success');
                loadExpenses();
                loadStatistics();
            } else {
                showMessage('삭제 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
}

function formatMoney(amount) {
    if (amount == null) return '0원';
    return parseInt(amount).toLocaleString() + '원';
}

function showMessage(text, type) {
    let colorClass = 'blue';
    if (type === 'success') colorClass = 'green';
    else if (type === 'error') colorClass = 'red';
    else if (type === 'info') colorClass = 'blue';

    $('#message').removeClass('green red blue')
                 .addClass(colorClass)
                 .addClass('white-text');
    $('#message-text').text(text);
    $('#message').show();

    setTimeout(function() {
        $('#message').fadeOut();
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>