<?php
$pageTitle = '고정지출';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section fade-in">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">repeat</i>고정지출 관리</h4>
                </div>
            </div>

            <!-- 추가/수정 폼 -->
            <div id="expense-form" class="card" style="display: none;">
                <div class="card-content">
                    <span class="card-title" id="form-title">고정지출 추가</span>
                    <form>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select id="category">
                                    <option value="" disabled selected>선택하세요</option>
                                    <option value="주거비">🏠 주거비</option>
                                    <option value="통신비">📱 통신비</option>
                                    <option value="보험료">🛡️ 보험료</option>
                                    <option value="구독">📺 구독</option>
                                    <option value="대출상환">🏦 대출상환</option>
                                    <option value="교육비">📚 교육비</option>
                                    <option value="기타">📝 기타</option>
                                </select>
                                <label>카테고리*</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="item-name" type="text" class="validate" required>
                                <label for="item-name">항목명*</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m4">
                                <input id="amount" type="number" class="validate" required>
                                <label for="amount">월 금액* (원)</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select id="payment-date">
                                    <option value="1">1일</option>
                                    <option value="5">5일</option>
                                    <option value="10">10일</option>
                                    <option value="15">15일</option>
                                    <option value="20">20일</option>
                                    <option value="25">25일</option>
                                    <option value="28">28일</option>
                                    <option value="31">말일</option>
                                </select>
                                <label>결제일</label>
                            </div>
                            <div class="input-field col s12 m4">
                                <select id="payment-method">
                                    <option value="체크">💳 체크카드</option>
                                    <option value="신용">🏦 신용카드</option>
                                    <option value="계좌이체">📱 계좌이체</option>
                                    <option value="현금">💵 현금</option>
                                </select>
                                <label>결제수단</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-field col s12 m6">
                                <select id="is-active">
                                    <option value="1">✅ 활성</option>
                                    <option value="0">❌ 비활성</option>
                                </select>
                                <label>활성상태</label>
                            </div>
                            <div class="input-field col s12 m6">
                                <input id="notes" type="text">
                                <label for="notes">비고</label>
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

            <!-- 액션 버튼 -->
            <div class="row">
                <div class="col s12">
                    <button id="add-expense-btn" class="btn waves-effect waves-light">
                        <i class="material-icons left">add</i>새 고정지출 추가
                    </button>
                    <button id="refresh-btn" class="btn grey waves-effect waves-light">
                        <i class="material-icons left">refresh</i>새로고침
                    </button>
                </div>
            </div>

            <!-- 고정지출 목록 테이블 -->
            <div class="row">
                <div class="col s12">
                    <div id="loading" class="center-align">
                        <div class="preloader-wrapper active">
                            <div class="spinner-layer spinner-indigo-only">
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
                        <p>고정지출 목록을 불러오는 중...</p>
                    </div>

                    <div class="card" id="expenses-table-card" style="display: none;">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>상태</th>
                                            <th>카테고리</th>
                                            <th>항목명</th>
                                            <th>월 금액</th>
                                            <th>결제일</th>
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
                                <i class="material-icons large">repeat</i>
                            </span>
                            <p class="grey-text">등록된 고정지출이 없습니다.</p>
                            <p class="grey-text">새 지출을 추가해보세요! 🔄</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 고정지출 통계 -->
            <div class="row">
                <div class="col s12 m6 l3">
                    <div class="stats-card indigo white-text">
                        <div class="stats-icon">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="stats-number" id="total-monthly">-</div>
                        <div class="stats-label">월 총 고정지출</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card green white-text">
                        <div class="stats-icon">
                            <i class="material-icons">check_circle</i>
                        </div>
                        <div class="stats-number" id="active-count">-</div>
                        <div class="stats-label">활성 지출항목</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card orange white-text">
                        <div class="stats-icon">
                            <i class="material-icons">category</i>
                        </div>
                        <div class="stats-number" id="max-category">-</div>
                        <div class="stats-label">최대 카테고리</div>
                    </div>
                </div>
                <div class="col s12 m6 l3">
                    <div class="stats-card teal white-text">
                        <div class="stats-icon">
                            <i class="material-icons">event</i>
                        </div>
                        <div class="stats-number" id="next-payment">-</div>
                        <div class="stats-label">다음 결제예정</div>
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

$(document).ready(function() {
    loadExpenses();

    // 이벤트 핸들러
    $('#add-expense-btn').click(showAddForm);
    $('#cancel-btn').click(hideForm);
    $('#save-btn').click(saveExpense);
    $('#refresh-btn').click(loadExpenses);
});

function loadExpenses() {
    $('#loading').show();
    $('#expenses-table-card').hide();
    $('#no-data').hide();

    $.ajax({
        url: '/api/fixed-expenses',
        method: 'GET',
        success: function(response) {
            $('#loading').hide();
            if (response.success) {
                if (response.data.length === 0) {
                    $('#no-data').show();
                    updateSummary([]);
                } else {
                    displayExpenses(response.data);
                    updateSummary(response.data);
                    $('#expenses-table-card').show();
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

function displayExpenses(expenses) {
    let tbody = $('#expenses-table-body');
    tbody.empty();

    expenses.forEach(function(expense) {
        let statusIcon = expense.is_active == 1 ? '✅' : '❌';
        let statusClass = expense.is_active == 1 ? 'positive' : 'negative';
        let paymentDateText = expense.payment_date == 31 ? '말일' : expense.payment_date + '일';

        let row = '<tr>' +
                  '<td class="' + statusClass + '" style="font-weight: bold;">' + statusIcon + '</td>' +
                  '<td>' + expense.category + '</td>' +
                  '<td>' + expense.item_name + '</td>' +
                  '<td style="font-weight: bold; color: #0066cc;">' + formatMoney(expense.amount) + '</td>' +
                  '<td>' + paymentDateText + '</td>' +
                  '<td>' + expense.payment_method + '</td>' +
                  '<td>' + (expense.notes || '-') + '</td>' +
                  '<td>' +
                  '<button onclick="editExpense(' + expense.id + ')" class="btn-small waves-effect waves-light blue" style="margin-right: 5px;"><i class="material-icons left">edit</i>수정</button>' +
                  '<button onclick="deleteExpense(' + expense.id + ')" class="btn-small waves-effect waves-light red"><i class="material-icons left">delete</i>삭제</button>' +
                  '</td>' +
                  '</tr>';
        tbody.append(row);
    });

}

function updateSummary(expenses) {
    let totalMonthly = 0;
    let activeCount = 0;
    let categoryTotals = {};

    expenses.forEach(function(expense) {
        if (expense.is_active == 1) {
            totalMonthly += parseInt(expense.amount) || 0;
            activeCount++;

            if (!categoryTotals[expense.category]) {
                categoryTotals[expense.category] = 0;
            }
            categoryTotals[expense.category] += parseInt(expense.amount) || 0;
        }
    });

    // 최대 카테고리 찾기
    let maxCategory = '-';
    let maxAmount = 0;
    for (let category in categoryTotals) {
        if (categoryTotals[category] > maxAmount) {
            maxAmount = categoryTotals[category];
            maxCategory = category;
        }
    }

    // 다음 결제 예정일 계산
    let today = new Date();
    let currentDay = today.getDate();
    let nextPayment = '-';

    let nextPayments = expenses
        .filter(e => e.is_active == 1)
        .map(e => parseInt(e.payment_date))
        .filter(day => day > currentDay)
        .sort((a, b) => a - b);

    if (nextPayments.length > 0) {
        nextPayment = nextPayments[0] + '일';
    } else {
        // 다음달 첫 결제일
        let nextMonthPayments = expenses
            .filter(e => e.is_active == 1)
            .map(e => parseInt(e.payment_date))
            .sort((a, b) => a - b);

        if (nextMonthPayments.length > 0) {
            nextPayment = '다음달 ' + nextMonthPayments[0] + '일';
        }
    }

    $('#total-monthly').text(formatMoney(totalMonthly));
    $('#active-count').text(activeCount + '개');
    $('#max-category').text(maxCategory);
    $('#next-payment').text(nextPayment);
}

function showAddForm() {
    editingExpenseId = null;
    $('#form-title').text('고정지출 추가');
    clearForm();
    $('#expense-form').show();
    $('#category').focus();
}

function editExpense(id) {
    editingExpenseId = id;
    $('#form-title').text('고정지출 수정');

    $.ajax({
        url: '/api/fixed-expenses/' + id,
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
    $('#category').val(expense.category);
    $('#item-name').val(expense.item_name);
    $('#amount').val(expense.amount);
    $('#payment-date').val(expense.payment_date);
    $('#payment-method').val(expense.payment_method);
    $('#is-active').val(expense.is_active);
    $('#notes').val(expense.notes || '');
}

function clearForm() {
    $('#category').val('주거비');
    $('#item-name').val('');
    $('#amount').val('');
    $('#payment-date').val('1');
    $('#payment-method').val('체크');
    $('#is-active').val('1');
    $('#notes').val('');
}

function hideForm() {
    $('#expense-form').hide();
    editingExpenseId = null;
}

function saveExpense() {
    let data = {
        category: $('#category').val(),
        item_name: $('#item-name').val().trim(),
        amount: parseInt($('#amount').val()) || 0,
        payment_date: parseInt($('#payment-date').val()),
        payment_method: $('#payment-method').val(),
        is_active: parseInt($('#is-active').val()),
        notes: $('#notes').val().trim()
    };

    // 유효성 검사
    if (!data.item_name || data.amount <= 0) {
        showMessage('필수 항목을 모두 입력해주세요.', 'error');
        return;
    }

    let url = '/api/fixed-expenses';
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
                showMessage(editingExpenseId ? '고정지출이 수정되었습니다.' : '고정지출이 추가되었습니다.', 'success');
                hideForm();
                loadExpenses();
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
    if (!confirm('이 고정지출을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '/api/fixed-expenses/' + id,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showMessage('고정지출이 삭제되었습니다.', 'success');
                loadExpenses();
            } else {
                showMessage('삭제 실패: ' + response.message, 'error');
            }
        },
        error: function() {
            showMessage('서버 오류가 발생했습니다.', 'error');
        }
    });
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