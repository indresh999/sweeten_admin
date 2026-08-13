<footer class="footer">
    <div class="footer-body d-flex justify-content-between align-items-center">
        <ul class="left-panel list-inline mb-0 p-0">
            <li class="list-inline-item"><a href="{{ route('pages.privacy-policy') }}">Privacy Policy</a></li>
            <li class="list-inline-item"><a href="{{ route('pages.term-of-use') }}">Terms of Use</a></li>
        </ul>
        <span class="text-muted small d-none d-sm-inline">&copy; {{ date('Y') }} {{ env('APP_NAME') }}</span>
    </div>
</footer>
