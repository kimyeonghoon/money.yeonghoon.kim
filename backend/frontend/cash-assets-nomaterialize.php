<?php
$pageTitle = '현금자산 (No Materialize)';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <!-- jQuery만 로드 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-primary { background-color: #007bff; color: white; }
    .btn-danger { background-color: #dc3545; color: white; }
    #loading { text-align: center; padding: 20px; }
    .card { border: 1px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏛️ 현금자산 관리 (Materialize 없음)</h1>

        <button id="refresh-btn" class="btn btn-primary">🔄 새로고침</button>

        <div id="loading">현금자산 목록을 불러오는 중...</div>

        <div id="assets-table-card" style="display: none;">
            <table id="assets-table">
                <thead>
                    <tr>
                        <th>구분</th>
                        <th>은행/기관</th>
                        <th>항목명</th>
                        <th>잔액</th>
                        <th>비고</th>
                        <th>수정일</th>
                    </tr>
                </thead>
                <tbody id="assets-table-body">
                </tbody>
            </table>
        </div>

        <div id="no-data" style="display: none;">
            <div class="card">등록된 현금자산이 없습니다.</div>
        </div>

        <div class="card">
            <h3>총 현금자산</h3>
            <div id="total-amount" style="font-size: 24px; font-weight: bold; color: #cc6600;">-</div>
        </div>
    </div>

<script>
$(document).ready(function() {
    console.log('📱 Document ready (No Materialize)');
    loadAssets();
    $('#refresh-btn').click(loadAssets);
});

function loadAssets() {
    console.log('🔄 loadAssets 시작');
    $('#loading').show();
    $('#assets-table-card').hide();
    $('#no-data').hide();

    $.ajax({
        url: '/api/cash-assets',
        method: 'GET',
        success: function(response) {
            console.log('✅ API 응답:', response);
            $('#loading').hide();
            if (response.success) {
                console.log('📊 데이터 개수:', response.data.length);
                if (response.data.length === 0) {
                    $('#no-data').show();
                    $('#total-amount').text('0원');
                } else {
                    displayAssets(response.data);
                    calculateTotal(response.data);
                    $('#assets-table-card').show();
                    console.log('👁️ 테이블 표시 완료');
                }
            } else {
                console.log('❌ API 오류:', response.message);
            }
        },
        error: function() {
            console.log('💥 AJAX 오류');
            $('#loading').hide();
        }
    });
}

function displayAssets(assets) {
    console.log('🏗️ displayAssets 시작, 자산 개수:', assets.length);
    let tbody = $('#assets-table-body');
    tbody.empty();

    assets.forEach(function(asset, index) {
        console.log('➕ 자산 추가 중:', index + 1, asset.item_name);
        let row = '<tr>' +
                  '<td>' + (asset.type || '-') + '</td>' +
                  '<td>' + (asset.account_name || asset.bank_name || '-') + '</td>' +
                  '<td>' + (asset.item_name || '-') + '</td>' +
                  '<td style="font-weight: bold; color: #cc6600;">' + formatMoney(asset.balance) + '</td>' +
                  '<td>' + (asset.notes || '-') + '</td>' +
                  '<td>' + formatDate(asset.updated_at || asset.created_at) + '</td>' +
                  '</tr>';
        tbody.append(row);
    });
    console.log('✅ displayAssets 완료');
}

function calculateTotal(assets) {
    let total = 0;
    assets.forEach(function(asset) {
        total += parseInt(asset.balance) || 0;
    });
    $('#total-amount').text(formatMoney(total));
}

function formatMoney(amount) {
    if (amount == null) return '0원';
    return parseInt(amount).toLocaleString() + '원';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    let date = new Date(dateStr);
    return date.getFullYear() + '-' +
           String(date.getMonth() + 1).padStart(2, '0') + '-' +
           String(date.getDate()).padStart(2, '0');
}
</script>

</body>
</html>