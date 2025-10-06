package com.yeonghoon.moneymanager

import android.annotation.SuppressLint
import android.graphics.Bitmap
import android.net.http.SslError
import android.os.Bundle
import android.view.KeyEvent
import android.view.View
import android.webkit.*
import android.widget.ProgressBar
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var swipeRefreshLayout: SwipeRefreshLayout

    // 웹 앱 URL - 프로덕션 환경
    private val baseUrl = "https://money.yeonghoon.kim"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        // View 초기화
        webView = findViewById(R.id.webView)
        progressBar = findViewById(R.id.progressBar)
        swipeRefreshLayout = findViewById(R.id.swipeRefreshLayout)

        // WebView 설정
        setupWebView()

        // SwipeRefresh 설정
        setupSwipeRefresh()

        // 초기 페이지 로드
        loadWebApp()
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        webView.apply {
            settings.apply {
                // JavaScript 활성화 (필수)
                javaScriptEnabled = true

                // DOM Storage 활성화 (로컬스토리지, 세션스토리지)
                domStorageEnabled = true

                // 쿠키 활성화 (세션 관리 필수)
                CookieManager.getInstance().setAcceptCookie(true)
                CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true)

                // 캐시 설정
                cacheMode = WebSettings.LOAD_DEFAULT

                // 확대/축소 비활성화 (모바일 최적화된 페이지)
                setSupportZoom(false)
                builtInZoomControls = false
                displayZoomControls = false

                // Viewport 설정
                useWideViewPort = true
                loadWithOverviewMode = true

                // 기타 설정
                loadsImagesAutomatically = true
                mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW

                // User Agent (웹뷰임을 명시)
                userAgentString = "$userAgentString MoneyManagerApp/1.0"
            }

            // WebViewClient 설정
            webViewClient = CustomWebViewClient()

            // WebChromeClient 설정 (진행률 표시)
            webChromeClient = CustomWebChromeClient()
        }
    }

    private fun setupSwipeRefresh() {
        swipeRefreshLayout.setOnRefreshListener {
            webView.reload()
        }

        swipeRefreshLayout.setColorSchemeResources(
            android.R.color.holo_blue_bright,
            android.R.color.holo_green_light,
            android.R.color.holo_orange_light,
            android.R.color.holo_red_light
        )
    }

    private fun loadWebApp() {
        webView.loadUrl(baseUrl)
    }

    // 뒤로가기 버튼 처리
    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
            webView.goBack()
            return true
        }
        return super.onKeyDown(keyCode, event)
    }

    // WebViewClient: 페이지 로딩 제어
    private inner class CustomWebViewClient : WebViewClient() {

        override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
            val url = request?.url?.toString() ?: return false

            // 같은 도메인만 허용
            return if (url.startsWith(baseUrl)) {
                view?.loadUrl(url)
                false
            } else {
                // 외부 링크는 차단 또는 외부 브라우저로 열기
                Toast.makeText(this@MainActivity, "외부 링크는 지원하지 않습니다", Toast.LENGTH_SHORT).show()
                true
            }
        }

        override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
            super.onPageStarted(view, url, favicon)
            progressBar.visibility = View.VISIBLE
        }

        override fun onPageFinished(view: WebView?, url: String?) {
            super.onPageFinished(view, url)
            progressBar.visibility = View.GONE
            swipeRefreshLayout.isRefreshing = false
        }

        override fun onReceivedError(
            view: WebView?,
            request: WebResourceRequest?,
            error: WebResourceError?
        ) {
            super.onReceivedError(view, request, error)

            if (request?.isForMainFrame == true) {
                showErrorDialog(
                    "페이지 로드 실패",
                    "네트워크 연결을 확인해주세요.\n(오류: ${error?.description})"
                )
            }
        }

        override fun onReceivedHttpError(
            view: WebView?,
            request: WebResourceRequest?,
            errorResponse: WebResourceResponse?
        ) {
            super.onReceivedHttpError(view, request, errorResponse)

            if (request?.isForMainFrame == true) {
                val statusCode = errorResponse?.statusCode ?: 0
                if (statusCode >= 400) {
                    showErrorDialog(
                        "서버 오류",
                        "서버에 문제가 발생했습니다.\n(HTTP $statusCode)"
                    )
                }
            }
        }

        override fun onReceivedSslError(view: WebView?, handler: SslErrorHandler?, error: SslError?) {
            // SSL 오류 발생 시 차단 (보안 중요)
            handler?.cancel()
            showErrorDialog(
                "보안 오류",
                "안전하지 않은 연결입니다.\nSSL 인증서를 확인해주세요."
            )
        }
    }

    // WebChromeClient: 진행률 및 알림 처리
    private inner class CustomWebChromeClient : WebChromeClient() {
        override fun onProgressChanged(view: WebView?, newProgress: Int) {
            super.onProgressChanged(view, newProgress)
            progressBar.progress = newProgress
        }

        override fun onJsAlert(
            view: WebView?,
            url: String?,
            message: String?,
            result: JsResult?
        ): Boolean {
            AlertDialog.Builder(this@MainActivity)
                .setMessage(message)
                .setPositiveButton("확인") { _, _ -> result?.confirm() }
                .setOnCancelListener { result?.cancel() }
                .create()
                .show()
            return true
        }

        override fun onJsConfirm(
            view: WebView?,
            url: String?,
            message: String?,
            result: JsResult?
        ): Boolean {
            AlertDialog.Builder(this@MainActivity)
                .setMessage(message)
                .setPositiveButton("확인") { _, _ -> result?.confirm() }
                .setNegativeButton("취소") { _, _ -> result?.cancel() }
                .setOnCancelListener { result?.cancel() }
                .create()
                .show()
            return true
        }
    }

    private fun showErrorDialog(title: String, message: String) {
        AlertDialog.Builder(this)
            .setTitle(title)
            .setMessage(message)
            .setPositiveButton("다시 시도") { _, _ -> webView.reload() }
            .setNegativeButton("닫기", null)
            .show()
    }

    override fun onDestroy() {
        // WebView 메모리 누수 방지
        webView.apply {
            stopLoading()
            clearCache(true)
            clearHistory()
            removeAllViews()
            destroy()
        }
        super.onDestroy()
    }
}
