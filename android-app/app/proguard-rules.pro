# Add project specific ProGuard rules here.
# WebView related rules
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}

# Keep WebView classes
-keep class android.webkit.** { *; }
-keep class androidx.webkit.** { *; }

# Keep application classes
-keep class com.yeonghoon.moneymanager.** { *; }
