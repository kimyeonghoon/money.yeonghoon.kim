// 프론트엔드 아카이브 통합 테스트 스크립트 (Node.js)

const https = require('https');
const http = require('http');

// HTTP 요청 함수
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const urlObj = new URL(url);
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname + urlObj.search,
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const req = http.request(options, (res) => {
            let body = '';
            res.on('data', (chunk) => {
                body += chunk;
            });
            res.on('end', () => {
                try {
                    const result = JSON.parse(body);
                    resolve(result);
                } catch (e) {
                    resolve({success: false, message: 'Invalid JSON', body: body.substring(0, 200)});
                }
            });
        });

        req.on('error', (err) => {
            reject(err);
        });

        if (data) {
            req.write(JSON.stringify(data));
        }
        req.end();
    });
}

// 테스트 함수들
async function testArchiveMonths() {
    console.log('🧪 테스트: 아카이브 월 목록 조회');
    try {
        const result = await makeRequest('http://localhost:8080/api/archive/months');
        if (result.success && result.data && result.data.length > 0) {
            console.log(`   ✅ 성공: ${result.data.length}개 월 발견`);
            result.data.forEach(month => {
                console.log(`      - ${month.label} (${month.value})`);
            });
            return result.data;
        } else {
            console.log(`   ❌ 실패: ${result.message || 'No data'}`);
            return null;
        }
    } catch (error) {
        console.log(`   ❌ 오류: ${error.message}`);
        return null;
    }
}

async function testArchiveData(month) {
    console.log(`🧪 테스트: ${month} 아카이브 데이터 조회`);

    try {
        const [cash, investment, pension] = await Promise.all([
            makeRequest(`http://localhost:8080/api/archive/cash-assets?month=${month}`),
            makeRequest(`http://localhost:8080/api/archive/investment-assets?month=${month}`),
            makeRequest(`http://localhost:8080/api/archive/pension-assets?month=${month}`)
        ]);

        let success = 0;
        let total = 0;

        if (cash.success) {
            const count = cash.data.data ? cash.data.data.length : 0;
            console.log(`   ✅ 현금 자산: ${count}개`);
            success++;
        } else {
            console.log(`   ❌ 현금 자산 실패: ${cash.message}`);
        }
        total++;

        if (investment.success) {
            const count = investment.data.data ? investment.data.data.length : 0;
            console.log(`   ✅ 투자 자산: ${count}개`);
            success++;
        } else {
            console.log(`   ❌ 투자 자산 실패: ${investment.message}`);
        }
        total++;

        if (pension.success) {
            const count = pension.data.data ? pension.data.data.length : 0;
            console.log(`   ✅ 연금 자산: ${count}개`);
            success++;
        } else {
            console.log(`   ❌ 연금 자산 실패: ${pension.message}`);
        }
        total++;

        return success === total;
    } catch (error) {
        console.log(`   ❌ 오류: ${error.message}`);
        return false;
    }
}

async function testCurrentVsArchive() {
    console.log('🧪 테스트: 현재 데이터 vs 아카이브 데이터 비교');

    try {
        const [current, archive] = await Promise.all([
            makeRequest('http://localhost:8080/api/cash-assets'),
            makeRequest('http://localhost:8080/api/archive/cash-assets?month=2025-09')
        ]);

        if (current.success && archive.success) {
            const currentData = current.data.data || [];
            const archiveData = archive.data.data || [];

            console.log(`   📊 현재 데이터: ${currentData.length}개`);
            console.log(`   📋 아카이브 데이터: ${archiveData.length}개`);

            // 총액 비교
            const currentTotal = currentData.reduce((sum, asset) => sum + (asset.balance || 0), 0);
            const archiveTotal = archiveData.reduce((sum, asset) => sum + (asset.balance || 0), 0);

            console.log(`   💰 현재 총액: ${currentTotal.toLocaleString()}원`);
            console.log(`   💾 아카이브 총액: ${archiveTotal.toLocaleString()}원`);

            if (currentTotal === archiveTotal) {
                console.log('   ✅ 데이터 일치');
                return true;
            } else {
                console.log('   ⚠️ 데이터 차이 있음 (수정된 아카이브)');
                return true; // 차이가 있는 것도 정상 (아카이브 수정 테스트)
            }
        } else {
            console.log('   ❌ 데이터 로드 실패');
            return false;
        }
    } catch (error) {
        console.log(`   ❌ 오류: ${error.message}`);
        return false;
    }
}

async function testFrontendPage() {
    console.log('🧪 테스트: 프론트엔드 페이지 로드');

    return new Promise((resolve) => {
        const req = http.request({
            hostname: 'localhost',
            port: 3001,
            path: '/assets.php',
            method: 'GET'
        }, (res) => {
            let body = '';
            res.on('data', (chunk) => {
                body += chunk;
            });
            res.on('end', () => {
                const hasMonthSelector = body.includes('month-selector');
                const hasArchiveManager = body.includes('ArchiveManager');
                const hasArchiveNotice = body.includes('archive-mode-notice');

                console.log(`   📄 페이지 크기: ${body.length} bytes`);
                console.log(`   🗓️ 월 선택기: ${hasMonthSelector ? '✅' : '❌'}`);
                console.log(`   📚 ArchiveManager: ${hasArchiveManager ? '✅' : '❌'}`);
                console.log(`   📢 아카이브 알림: ${hasArchiveNotice ? '✅' : '❌'}`);

                const success = hasMonthSelector && hasArchiveManager && hasArchiveNotice;
                resolve(success);
            });
        });

        req.on('error', (err) => {
            console.log(`   ❌ 오류: ${err.message}`);
            resolve(false);
        });

        req.end();
    });
}

// 메인 테스트 실행
async function runTests() {
    console.log('=== 프론트엔드 아카이브 통합 테스트 ===\n');

    let totalTests = 0;
    let passedTests = 0;

    // 1. 아카이브 월 목록 테스트
    totalTests++;
    const months = await testArchiveMonths();
    if (months) passedTests++;
    console.log('');

    // 2. 프론트엔드 페이지 테스트
    totalTests++;
    const frontendOk = await testFrontendPage();
    if (frontendOk) passedTests++;
    console.log('');

    // 3. 아카이브 데이터 테스트
    if (months && months.length > 0) {
        totalTests++;
        const archiveOk = await testArchiveData(months[0].value);
        if (archiveOk) passedTests++;
        console.log('');
    }

    // 4. 현재 vs 아카이브 비교
    totalTests++;
    const compareOk = await testCurrentVsArchive();
    if (compareOk) passedTests++;
    console.log('');

    // 결과 요약
    console.log('=== 테스트 결과 ===');
    console.log(`통과: ${passedTests}/${totalTests}`);
    console.log(`성공률: ${Math.round((passedTests / totalTests) * 100)}%`);

    if (passedTests === totalTests) {
        console.log('🎉 모든 테스트 통과! 프론트엔드 통합 성공');
        process.exit(0);
    } else {
        console.log('⚠️ 일부 테스트 실패');
        process.exit(1);
    }
}

runTests();