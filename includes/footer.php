    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Herbal Haven</h3>
                    <p>Your trusted source for herbal remedy information and natural wellness guidance.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/catalogue.php">Herb Catalogue</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/chat-assistant.php">AI Assistant</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Important</h4>
                    <p class="footer-disclaimer">This platform provides educational information only. Always consult healthcare professionals before using herbal remedies.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Herbal Haven. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating AI Assistant Button -->
    <a href="<?php echo SITE_URL; ?>/chat-assistant.php" class="floating-ai-button" title="AI Assistant">
        <span class="ai-icon">🤖</span>
        <span class="ai-text">AI Assistant</span>
    </a>

    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/js/mobile-enhancements.js"></script>
    <?php if (isset($includeChatJS) && $includeChatJS): ?>
    <script src="<?php echo SITE_URL; ?>/js/chat.js"></script>
    <?php endif; ?>
</body>
</html>

