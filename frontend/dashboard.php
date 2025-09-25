<?php
$pageTitle = '대시보드';
include 'includes/header.php';
?>

    <main class="container">
        <div class="section fade-in">
            <div class="row">
                <div class="col s12">
                    <h4 class="section-title"><i class="material-icons left">dashboard</i>자산관리 대시보드</h4>
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

            <!-- 대시보드 컨텐츠 -->
            <div id="dashboard-content" style="display: none;">
                <!-- 자산 현황 -->
                <div class="dashboard-section">
                    <h5 class="section-title">💎 자산 현황</h5>
                    <div class="row">
                        <div class="col s12 m6 l3">
                            <div class="stats-card blue white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">account_balance</i>
                                </div>
                                <div class="stats-number" id="total-assets">-</div>
                                <div class="stats-label">총 자산</div>
                                <div id="assets-change" class="stats-change">-</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card green white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">account_balance_wallet</i>
                                </div>
                                <div class="stats-number" id="cash-assets">-</div>
                                <div class="stats-label">현금자산</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card orange white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">trending_up</i>
                                </div>
                                <div class="stats-number" id="investment-assets">-</div>
                                <div class="stats-label">투자자산</div>
                                <div id="investment-return" class="stats-change">-</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card purple white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">security</i>
                                </div>
                                <div class="stats-number" id="pension-assets">-</div>
                                <div class="stats-label">연금자산</div>
                                <div id="pension-return" class="stats-change">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 지출 현황 -->
                <div class="dashboard-section">
                    <h5 class="section-title">📅 지출 현황</h5>
                    <div class="row">
                        <div class="col s12 m6 l3">
                            <div class="stats-card red white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">shopping_cart</i>
                                </div>
                                <div class="stats-number" id="monthly-expenses">-</div>
                                <div class="stats-label">이번달 지출</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card indigo white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">repeat</i>
                                </div>
                                <div class="stats-number" id="fixed-expenses">-</div>
                                <div class="stats-label">고정지출</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card teal white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">payment</i>
                                </div>
                                <div class="stats-number" id="prepaid-expenses">-</div>
                                <div class="stats-label">선납지출</div>
                            </div>
                        </div>
                        <div class="col s12 m6 l3">
                            <div class="stats-card deep-orange white-text">
                                <div class="stats-icon">
                                    <i class="material-icons">today</i>
                                </div>
                                <div class="stats-number" id="daily-average">-</div>
                                <div class="stats-label">일평균지출</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 최근 거래 내역 -->
                <div class="dashboard-section">
                    <h5 class="section-title">📋 최근 거래 내역</h5>
                    <div class="card">
                        <div class="card-content">
                            <div class="responsive-table">
                                <table class="striped">
                                    <thead>
                                        <tr>
                                            <th>날짜</th>
                                            <th>구분</th>
                                            <th>항목명</th>
                                            <th>금액</th>
                                            <th>비고</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-transactions-body">
                                        <tr>
                                            <td colspan="5" class="center-align">거래 내역을 불러오는 중...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 빠른 액션 버튼 -->
                <div class="dashboard-section">
                    <div class="row center-align">
                        <div class="col s12 m4">
                            <a href="cash-assets.php" class="btn waves-effect waves-light">
                                <i class="material-icons left">account_balance_wallet</i>현금자산 관리
                            </a>
                        </div>
                        <div class="col s12 m4">
                            <a href="daily-expenses.php" class="btn waves-effect waves-light">
                                <i class="material-icons left">receipt</i>지출 기록
                            </a>
                        </div>
                        <div class="col s12 m4">
                            <a href="investment-assets.php" class="btn waves-effect waves-light">
                                <i class="material-icons left">trending_up</i>투자자산 관리
                            </a>
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

<script>
$(document).ready(function() {
    loadDashboardData();
});

function loadDashboardData() {
    // 대시보드 데이터 로드
    $.ajax({
        url: '/api/dashboard',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateDashboard(response.data);
                $('#loading').hide();
                $('#dashboard-content').show();
            } else {
                showError('대시보드 데이터 로드 실패: ' + response.message);
            }
        },
        error: function() {
            showError('서버와의 연결에 실패했습니다.');
        }
    });

    // 최근 거래내역 로드
    loadRecentTransactions();
}

function updateDashboard(data) {
    // 금액 포맷팅 - API 응답 구조에 맞게 수정
    $('#total-assets').text(formatMoney(data.total_assets));
    $('#cash-assets').text(formatMoney(data.assets_breakdown?.cash || 0));
    $('#investment-assets').text(formatMoney(data.assets_breakdown?.investment || 0));
    $('#pension-assets').text(formatMoney(data.assets_breakdown?.pension || 0));

    // 지출 데이터 구조에 맞게 수정
    const monthlyExpenseTotal = (parseInt(data.monthly_expenses?.daily_total || 0) +
                                parseInt(data.monthly_expenses?.fixed_total || 0));
    $('#monthly-expenses').text(formatMoney(monthlyExpenseTotal));
    $('#fixed-expenses').text(formatMoney(data.monthly_expenses?.fixed_total || 0));
    $('#prepaid-expenses').text(formatMoney(data.monthly_expenses?.prepaid_total || 0));

    // 일평균 지출 계산 (월 지출 / 30일)
    const dailyAvg = Math.floor(monthlyExpenseTotal / 30);
    $('#daily-average').text(formatMoney(dailyAvg));

    // 수익률 표시
    if (data.investment_return_rate !== undefined) {
        const investReturn = parseFloat(data.investment_return_rate);
        $('#investment-return').text(`수익률: ${investReturn.toFixed(2)}%`)
                               .removeClass('positive negative')
                               .addClass(investReturn >= 0 ? 'positive' : 'negative');
    }

    if (data.pension_return_rate !== undefined) {
        const pensionReturn = parseFloat(data.pension_return_rate);
        $('#pension-return').text(`수익률: ${pensionReturn.toFixed(2)}%`)
                            .removeClass('positive negative')
                            .addClass(pensionReturn >= 0 ? 'positive' : 'negative');
    }
}

function loadRecentTransactions() {
    // 대시보드 API에서 recent_activities 사용
    $.ajax({
        url: '/api/dashboard',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data.recent_activities) {
                let recentExpenses = response.data.recent_activities.recent_expenses || [];
                let upcomingPayments = response.data.recent_activities.upcoming_payments || [];

                // 최근 지출과 곧 있을 결제를 합쳐서 표시
                let allTransactions = [];

                // 최근 지출 추가
                recentExpenses.forEach(function(expense) {
                    allTransactions.push({
                        date: expense.expense_date,
                        type: '지출',
                        item_name: expense.item_name,
                        amount: expense.amount,
                        notes: expense.notes
                    });
                });

                // 곧 있을 고정 결제 추가 (최대 3개)
                upcomingPayments.slice(0, 3).forEach(function(payment) {
                    allTransactions.push({
                        date: '예정',
                        type: '고정지출',
                        item_name: payment.item_name,
                        amount: payment.amount,
                        notes: payment.category
                    });
                });

                updateTransactionTable(allTransactions);
            } else {
                updateTransactionTable([]);
            }
        },
        error: function() {
            updateTransactionTable([]);
        }
    });
}

function updateTransactionTable(transactions) {
    let tbody = $('#recent-transactions-body');
    tbody.empty();

    if (transactions.length === 0) {
        tbody.append('<tr><td colspan="5">최근 거래 내역이 없습니다.</td></tr>');
        return;
    }

    transactions.forEach(function(transaction) {
        let row = '<tr>' +
                  '<td>' + (transaction.date || '-') + '</td>' +
                  '<td>' + (transaction.type || '-') + '</td>' +
                  '<td>' + (transaction.item_name || '항목명 없음') + '</td>' +
                  '<td class="negative">-' + formatMoney(transaction.amount || 0) + '</td>' +
                  '<td>' + (transaction.notes || '-') + '</td>' +
                  '</tr>';
        tbody.append(row);
    });
}

function formatMoney(amount) {
    if (amount == null || amount === '') return '0원';
    return parseInt(amount).toLocaleString() + '원';
}

function showError(message) {
    $('#error-message').text(message).show();
    $('#loading').hide();
}
</script>

<?php include 'includes/footer.php'; ?>