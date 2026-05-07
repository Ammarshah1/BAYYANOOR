/**
 * Bayyanoor Frontend Application Logic v2.1
 * Modular architecture: Navigation, Dashboard, AI Chat, Charts, Auth, Notifications
 */

(function($) {
    'use strict';

    // =========================================================
    // MODULE: Toast Notifications
    // =========================================================
    var Toast = {
        show: function(message, type) {
            type = type || 'success';
            var icons = { success: 'OK', error: '!', info: 'i', badge: 'BD', xp: 'XP' };
            var icon = icons[type] || icons.info;
            var safeType = type.replace(/[^a-z0-9_-]/gi, '');
            
            var $toast = $('<div class="b-toast b-toast-' + safeType + '">' +
                '<span class="b-toast-icon">' + escHtml(icon) + '</span>' +
                '<span class="b-toast-msg">' + escHtml(message) + '</span>' +
            '</div>');
            
            if (!$('#b-toast-container').length) {
                $('body').append('<div id="b-toast-container"></div>');
            }
            
            $('#b-toast-container').append($toast);
            setTimeout(function() { $toast.addClass('show'); }, 50);
            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() { $toast.remove(); }, 400);
            }, 4000);
        }
    };

    // =========================================================
    // MODULE: Navigation (SPA-like pushState)
    // =========================================================
    var Nav = {
        $root: null,
        
        init: function() {
            this.$root = $('#bayyanoor-app-root');
            if (!this.$root.length) return;
            
            var self = this;
            
            $(document).off('click.bayyanoorNav', '.ajax-link').on('click.bayyanoorNav', '.ajax-link', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (!url || url === '#' || url.charAt(0) === '#') return;
                
                // Update active states
                self.setActive($(this));
                
                if (url !== window.location.href) {
                    window.history.pushState({ path: url }, '', url);
                    self.loadContent(url);
                }
            });
            
            $(window).off('popstate.bayyanoorNav').on('popstate.bayyanoorNav', function() {
                self.loadContent(window.location.pathname);
                self.syncActive();
            });
        },
        
        setActive: function($el) {
            // Sidebar
            $('.b-side-nav .ajax-link').removeClass('active');
            var href = $el.attr('href');
            $('.b-side-nav .ajax-link').each(function() {
                if ($(this).attr('href') === href) $(this).addClass('active');
            });
            // Top nav
            $('.b-top-nav .ajax-link').removeClass('active');
            $('.b-top-nav .ajax-link').each(function() {
                if ($(this).attr('href') === href) $(this).addClass('active');
            });
        },
        
        syncActive: function() {
            var path = window.location.pathname;
            $('.ajax-link').removeClass('active');
            $('.ajax-link').each(function() {
                var href = $(this).attr('href');
                if (href && path.indexOf(href.replace(/https?:\/\/[^\/]+/, '')) !== -1) {
                    $(this).addClass('active');
                }
            });
        },
        
        loadContent: function(url) {
            var self = this;
            self.$root.addClass('b-loading');
            
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    var newContent = $(response).find('#bayyanoor-app-root').html();
                    if (newContent) {
                        self.$root.html(newContent);
                        App.initComponents();
                    }
                    self.$root.removeClass('b-loading');
                },
                error: function() {
                    self.$root.html('<div class="bayyanoor-card"><h2>Error</h2><p>Could not load content. Please try again.</p></div>');
                    self.$root.removeClass('b-loading');
                }
            });
        }
    };

    // =========================================================
    // MODULE: Dashboard
    // =========================================================
    var Dashboard = {
        init: function() {
            var $dashboard = $('#b-dashboard');
            if (!$dashboard.length) return;
            
            this.loadData();
            this.bindModals();
        },
        
        loadData: function() {
            if (typeof bayyanoorAjax === 'undefined') return;
            
            $.ajax({
                url: bayyanoorAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'bayyanoor_get_dashboard_data',
                    nonce: bayyanoorAjax.nonce
                },
                success: function(res) {
                    if (!res.success) {
                        // Session expired or not authenticated
                        if (res.data && (res.data === 'Not authenticated.' || res.data.indexOf('nonce') !== -1)) {
                            Toast.show('Session expired. Redirecting to login...', 'error');
                            setTimeout(function() {
                                window.location.href = bayyanoorAjax.home_url + '/bayyanoor-login';
                            }, 1500);
                            return;
                        }
                        Dashboard.showError('Could not load dashboard data. Please refresh the page.');
                        return;
                    }
                    var d = res.data;
                    
                    // User
                    if (d.user) {
                        $('#b-user-name').text(d.user.first_name);
                        $('.b-avatar').text(d.user.initials);
                        $('.b-side-footer .greet').html('Salaam,<br>' + escHtml(d.user.first_name) + '!');
                    }
                    
                    // Streak & XP
                    if (d.streak) {
                        Dashboard.animateNumber('#b-streak-count', d.streak.current_streak);
                        Dashboard.animateNumber('#b-xp-count', d.streak.total_xp);
                        Dashboard.animateNumber('#b-level-count', d.streak.current_level);
                        $('#b-xp-level').text(d.streak.current_level);
                        $('#b-xp-current').text(d.streak.total_xp);
                        $('#b-xp-next').text(d.streak.xp_for_next);
                        setTimeout(function() {
                            $('#b-xp-fill').css('width', clampPercent(d.streak.xp_progress) + '%');
                        }, 300);
                    }
                    
                    // Hifz
                    if (d.hifz) {
                        $('#b-hifz-count').text(d.hifz.surahs_started);
                    }
                    
                    // Courses
                    if (d.courses) {
                        Dashboard.renderCourses(d.courses);
                    }
                    
                    // Mastery Chart
                    if (d.mastery) {
                        Charts.renderRadar(d.mastery);
                    }
                    
                    // Badges
                    if (d.badges) {
                        Dashboard.renderBadges(d.badges);
                    }
                    
                    // Activity
                    if (d.activity) {
                        Dashboard.renderActivity(d.activity);
                    }

                    console.log('[Bayyanoor] Dashboard loaded successfully.');
                },
                error: function(xhr) {
                    console.error('[Bayyanoor] Dashboard load failed:', xhr.status);
                    if (xhr.status === 0 || xhr.status === 403) {
                        Dashboard.showError('Your session may have expired. <a href="' + (bayyanoorAjax.home_url || '') + '/bayyanoor-login">Log in again</a>.');
                    } else {
                        Dashboard.showError('Something went wrong loading your dashboard. Please try refreshing.');
                    }
                }
            });
        },

        showError: function(message) {
            var $grid = $('#b-course-grid');
            if ($grid.length) {
                $grid.html('<p class="b-empty-state"><span class="b-empty-icon">⚠️</span>' + message + '</p>');
            }
        },
        
        renderCourses: function(courses) {
            var $grid = $('#b-course-grid');
            $grid.empty();
            
            if (!courses.length) {
                $grid.html('<p class="b-empty">No courses available yet.</p>');
                return;
            }
            
            courses.forEach(function(c, i) {
                var progress = clampPercent(c.progress);
                var lessons = parseInt(c.lessons, 10) || 0;
                var link = escAttr(c.link || '#');
                var card = '<div class="b-course-card b-fade-in" style="animation-delay:' + (i * 0.1) + 's">' +
                    '<div class="icon">' + escHtml(c.icon || 'BN') + '</div>' +
                    '<h3>' + escHtml(c.title) + '</h3>' +
                    '<div class="meta"><span>' + progress + '%</span><span>' + lessons + ' lessons</span></div>' +
                    '<div class="b-prog"><div class="fill" style="width:' + progress + '%"></div></div>' +
                    '<a href="' + link + '" class="b-btn-gold">Continue Learning</a>' +
                '</div>';
                $grid.append(card);
            });
        },
        
        renderBadges: function(badges) {
            var $grid = $('#b-badge-grid');
            $grid.empty();
            
            if (!badges.length) {
                $grid.html('<div class="b-badge-empty">Complete activities to earn badges.</div>');
                return;
            }
            
            badges.forEach(function(b) {
                $grid.append('<div class="b-badge-item" title="' + escAttr(b.name) + '"><span class="b-badge-icon">' + escHtml(b.icon || 'BN') + '</span><span class="b-badge-name">' + escHtml(b.name) + '</span></div>');
            });
        },
        
        renderActivity: function(activity) {
            var $feed = $('#b-activity-feed');
            $feed.empty();
            
            if (!activity.length) {
                $feed.html('<li class="b-activity-empty">No activity yet. Start learning.</li>');
                return;
            }
            
            activity.forEach(function(a) {
                var xpBadge = a.xp ? '<span class="b-xp-badge">' + escHtml(a.xp) + '</span>' : '';
                var cls = a.type === 'badge' ? ' class="b-act-badge"' : (a.type === 'xp' ? ' class="completed"' : '');
                $feed.append('<li' + cls + '><strong>' + escHtml(a.text) + '</strong>' + xpBadge + '<span class="b-act-time">' + escHtml(a.time) + '</span></li>');
            });
        },
        
        animateNumber: function(selector, target) {
            var $el = $(selector);
            var current = parseInt($el.text()) || 0;
            if (current === target) return;
            
            var duration = 800;
            var step = Math.ceil(Math.abs(target - current) / (duration / 30));
            var timer = setInterval(function() {
                if (current < target) {
                    current = Math.min(current + step, target);
                } else {
                    current = Math.max(current - step, target);
                }
                $el.text(current);
                if (current === target) clearInterval(timer);
            }, 30);
        },
        
        bindModals: function() {
            // Hifz modal
            $(document).off('click.bayyanoorHifzOpen', '#b-log-hifz-btn').on('click.bayyanoorHifzOpen', '#b-log-hifz-btn', function(e) {
                e.preventDefault();
                $('#b-hifz-modal').fadeIn(200);
            });
            
            // Quiz modal
            $(document).off('click.bayyanoorQuizOpen', '#b-take-quiz-btn, #b-practice-tajweed').on('click.bayyanoorQuizOpen', '#b-take-quiz-btn, #b-practice-tajweed', function(e) {
                e.preventDefault();
                $('#b-quiz-modal').fadeIn(200);
            });
            
            // Close modals
            $(document).off('click.bayyanoorModalClose', '.b-modal-close').on('click.bayyanoorModalClose', '.b-modal-close', function() {
                var modal = $(this).data('modal');
                $('#' + modal).fadeOut(200);
            });
            $(document).off('click.bayyanoorModalOverlay', '.b-modal-overlay').on('click.bayyanoorModalOverlay', '.b-modal-overlay', function(e) {
                if ($(e.target).hasClass('b-modal-overlay')) {
                    $(this).fadeOut(200);
                }
            });
            
            // Hifz form
            $(document).off('submit.bayyanoorHifzForm', '#b-hifz-form').on('submit.bayyanoorHifzForm', '#b-hifz-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true).text('Logging...');
                
                $.ajax({
                    url: bayyanoorAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bayyanoor_log_hifz',
                        nonce: bayyanoorAjax.nonce,
                        surah_no: $form.find('[name="surah_no"]').val(),
                        ayah_range: $form.find('[name="ayah_range"]').val(),
                        status: $form.find('[name="status"]').val(),
                        quality: $form.find('[name="quality"]').val()
                    },
                    success: function(res) {
                        if (res.success) {
                            Toast.show('Hifz progress logged! +XP earned', 'xp');
                            $('#b-hifz-modal').fadeOut(200);
                            $form[0].reset();
                            Dashboard.loadData();
                        } else {
                            Toast.show(res.data || 'Error logging Hifz', 'error');
                        }
                        $btn.prop('disabled', false).text('Log Progress');
                    },
                    error: function() {
                        Toast.show('Network error', 'error');
                        $btn.prop('disabled', false).text('Log Progress');
                    }
                });
            });
            
            // Quiz form
            $(document).off('submit.bayyanoorQuizForm', '#b-quiz-form').on('submit.bayyanoorQuizForm', '#b-quiz-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true).text('Submitting...');
                
                $.ajax({
                    url: bayyanoorAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bayyanoor_submit_quiz',
                        nonce: bayyanoorAjax.nonce,
                        skill_slug: $form.find('[name="skill_slug"]').val(),
                        score: $form.find('[name="score"]').val()
                    },
                    success: function(res) {
                        if (res.success) {
                            var m = res.data.mastery;
                            var msg = 'Quiz submitted! Level: ' + m.label;
                            if (m.leveled_up) {
                                msg += ' Level up!';
                                Toast.show('Mastery Level Up: ' + m.label, 'badge');
                            }
                            Toast.show(msg, 'xp');
                            $('#b-quiz-modal').fadeOut(200);
                            Dashboard.loadData();
                        } else {
                            Toast.show(res.data || 'Error submitting quiz', 'error');
                        }
                        $btn.prop('disabled', false).text('Submit Quiz');
                    },
                    error: function() {
                        Toast.show('Network error', 'error');
                        $btn.prop('disabled', false).text('Submit Quiz');
                    }
                });
            });
        }
    };

    // =========================================================
    // MODULE: Charts
    // =========================================================
    var Charts = {
        renderRadar: function(data) {
            var canvas = document.getElementById('masteryRadarChart');
            if (!canvas || !canvas.getContext || !data || !data.labels || !data.data) return;

            var ctx = canvas.getContext('2d');
            var size = Math.max(220, Math.floor(canvas.getBoundingClientRect().width || 280));
            var dpr = window.devicePixelRatio || 1;
            canvas.width = size * dpr;
            canvas.height = size * dpr;
            canvas.style.width = size + 'px';
            canvas.style.height = size + 'px';
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, size, size);

            var labels = data.labels.slice(0, 8);
            var values = data.data.slice(0, labels.length).map(clampPercent);
            var count = labels.length;
            if (!count) return;

            var center = size / 2;
            var radius = size * 0.31;
            var labelRadius = radius + 34;
            var startAngle = -Math.PI / 2;

            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(23, 35, 30, 0.10)';
            ctx.fillStyle = 'rgba(17, 94, 65, 0.04)';

            for (var ring = 4; ring >= 1; ring--) {
                drawPolygon(ctx, count, center, radius * ring / 4, startAngle);
                ctx.stroke();
            }

            for (var i = 0; i < count; i++) {
                var point = polarPoint(center, radius, startAngle + (Math.PI * 2 * i / count));
                ctx.beginPath();
                ctx.moveTo(center, center);
                ctx.lineTo(point.x, point.y);
                ctx.stroke();
            }

            ctx.beginPath();
            values.forEach(function(value, i) {
                var point = polarPoint(center, radius * value / 100, startAngle + (Math.PI * 2 * i / count));
                if (i === 0) {
                    ctx.moveTo(point.x, point.y);
                } else {
                    ctx.lineTo(point.x, point.y);
                }
            });
            ctx.closePath();
            ctx.fillStyle = 'rgba(17, 94, 65, 0.16)';
            ctx.strokeStyle = '#115e41';
            ctx.lineWidth = 2;
            ctx.fill();
            ctx.stroke();

            values.forEach(function(value, i) {
                var angle = startAngle + (Math.PI * 2 * i / count);
                var point = polarPoint(center, radius * value / 100, angle);
                ctx.beginPath();
                ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = '#c69d67';
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#fffdf9';
                ctx.stroke();

                var labelPoint = polarPoint(center, labelRadius, angle);
                ctx.fillStyle = '#41524b';
                ctx.font = '700 11px Inter, system-ui, sans-serif';
                ctx.textAlign = labelPoint.x < center - 6 ? 'right' : (labelPoint.x > center + 6 ? 'left' : 'center');
                ctx.textBaseline = labelPoint.y < center - 6 ? 'bottom' : (labelPoint.y > center + 6 ? 'top' : 'middle');
                ctx.fillText(String(labels[i]).slice(0, 16), labelPoint.x, labelPoint.y);
            });
        }
    };

    // =========================================================
    // MODULE: AI Chat
    // =========================================================
    var AIChat = {
        init: function() {
            var $chatForm = $('#bayyanoor-ai-form');
            if (!$chatForm.length) return;
            
            var $history = $('.chat-history');
            var $input = $('#ai-query');
            var $submitBtn = $chatForm.find('button[type="submit"]');
            
            $chatForm.off('submit').on('submit', function(e) {
                e.preventDefault();
                var query = $input.val().trim();
                if (!query) return;
                
                $history.append('<div class="chat-msg user b-fade-in">' + escHtml(query) + '</div>');
                $input.val('');
                scrollToBottom($history);
                
                var typingHtml = '<div class="chat-msg ai typing-indicator" id="ai-typing"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
                $history.append(typingHtml);
                scrollToBottom($history);
                $submitBtn.prop('disabled', true);
                
                $.ajax({
                    url: bayyanoorAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bayyanoor_ai_query',
                        nonce: bayyanoorAjax.nonce,
                        query: query
                    },
                    success: function(res) {
                        $('#ai-typing').remove();
                        if (res.success) {
                            var formatted = formatAIResponse(res.data.response);
                            $history.append('<div class="chat-msg ai b-fade-in">' + formatted + '</div>');
                        } else {
                            $history.append('<div class="chat-msg ai b-fade-in">Sorry, an error occurred: ' + escHtml(res.data) + '</div>');
                        }
                        scrollToBottom($history);
                        $submitBtn.prop('disabled', false);
                        $input.focus();
                    },
                    error: function() {
                        $('#ai-typing').remove();
                        $history.append('<div class="chat-msg ai">Communication error. Please try again.</div>');
                        scrollToBottom($history);
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        }
    };

    // =========================================================
    // MODULE: Auth (Login / Register)
    // =========================================================
    var Auth = {
        init: function() {
            this.bindTabs();
            this.bindLoginForm();
            this.bindRegisterForm();
        },
        
        bindTabs: function() {
            $(document).off('click.bayyanoorAuthTabs', '.b-auth-tab').on('click.bayyanoorAuthTabs', '.b-auth-tab', function() {
                var tab = $(this).data('tab');
                $('.b-auth-tab').removeClass('active');
                $(this).addClass('active');
                $('.b-auth-panel').removeClass('active');
                $('#b-' + tab + '-panel').addClass('active');
            });
        },
        
        bindLoginForm: function() {
            $(document).off('submit.bayyanoorLogin', '#bayyanoor-login-form').on('submit.bayyanoorLogin', '#bayyanoor-login-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('#login-submit-btn');
                var $error = $('#login-error');
                
                $btn.find('.btn-text').hide();
                $btn.find('.btn-loader').show();
                $btn.prop('disabled', true);
                $error.hide();
                
                $.ajax({
                    url: bayyanoorAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bayyanoor_login',
                        nonce: bayyanoorAjax.nonce,
                        username: $form.find('[name="username"]').val(),
                        password: $form.find('[name="password"]').val(),
                        remember: $form.find('[name="remember"]').is(':checked') ? 'true' : 'false'
                    },
                    success: function(res) {
                        if (res.success) {
                            Toast.show(res.data.message, 'success');
                            setTimeout(function() {
                                window.location.href = res.data.redirect;
                            }, 800);
                        } else {
                            $error.text(res.data).slideDown(200);
                            $btn.find('.btn-text').show();
                            $btn.find('.btn-loader').hide();
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $error.text('Network error. Please try again.').slideDown(200);
                        $btn.find('.btn-text').show();
                        $btn.find('.btn-loader').hide();
                        $btn.prop('disabled', false);
                    }
                });
            });
        },
        
        bindRegisterForm: function() {
            $(document).off('submit.bayyanoorRegister', '#bayyanoor-register-form').on('submit.bayyanoorRegister', '#bayyanoor-register-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('#register-submit-btn');
                var $error = $('#register-error');
                
                $btn.find('.btn-text').hide();
                $btn.find('.btn-loader').show();
                $btn.prop('disabled', true);
                $error.hide();
                
                $.ajax({
                    url: bayyanoorAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bayyanoor_register',
                        nonce: bayyanoorAjax.nonce,
                        first_name: $form.find('[name="first_name"]').val(),
                        last_name: $form.find('[name="last_name"]').val(),
                        email: $form.find('[name="email"]').val(),
                        username: $form.find('[name="username"]').val(),
                        password: $form.find('[name="password"]').val(),
                        confirm_password: $form.find('[name="confirm_password"]').val()
                    },
                    success: function(res) {
                        if (res.success) {
                            Toast.show(res.data.message, 'success');
                            setTimeout(function() {
                                window.location.href = res.data.redirect;
                            }, 1000);
                        } else {
                            $error.text(res.data).slideDown(200);
                            $btn.find('.btn-text').show();
                            $btn.find('.btn-loader').hide();
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        $error.text('Network error. Please try again.').slideDown(200);
                        $btn.find('.btn-text').show();
                        $btn.find('.btn-loader').hide();
                        $btn.prop('disabled', false);
                    }
                });
            });
        }
    };

    // =========================================================
    // Utility Functions
    // =========================================================
    function scrollToBottom($el) {
        if ($el.length && $el[0]) {
            $el.scrollTop($el[0].scrollHeight);
        }
    }

    function escHtml(unsafe) {
        return (unsafe || '').toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escAttr(unsafe) {
        return escHtml(unsafe).replace(/`/g, "&#096;");
    }

    function clampPercent(value) {
        var number = parseFloat(value);
        if (!isFinite(number)) return 0;
        return Math.max(0, Math.min(100, Math.round(number)));
    }

    function polarPoint(center, radius, angle) {
        return {
            x: center + Math.cos(angle) * radius,
            y: center + Math.sin(angle) * radius
        };
    }

    function drawPolygon(ctx, points, center, radius, startAngle) {
        ctx.beginPath();
        for (var i = 0; i < points; i++) {
            var point = polarPoint(center, radius, startAngle + (Math.PI * 2 * i / points));
            if (i === 0) {
                ctx.moveTo(point.x, point.y);
            } else {
                ctx.lineTo(point.x, point.y);
            }
        }
        ctx.closePath();
    }

    function formatAIResponse(text) {
        // Simple markdown-like formatting for AI responses
        var formatted = escHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n{2,}/g, '</p><p>')
            .replace(/\n• /g, '<br>• ')
            .replace(/\n🔹 /g, '<br>🔹 ')
            .replace(/\n(\d+)\. /g, '<br>$1. ')
            .replace(/\n/g, '<br>');

        return '<p>' + formatted + '</p>';
    }

    // =========================================================
    // MODULE: Notifications (Bell Dropdown)
    // =========================================================
    var Notifications = {
        init: function() {
            var $toggle = $('#b-notif-toggle');
            var $dropdown = $('#b-notif-dropdown');
            if (!$toggle.length || !$dropdown.length) return;

            $toggle.off('click.bayyanoorNotif').on('click.bayyanoorNotif', function(e) {
                e.stopPropagation();
                var visible = $dropdown.is(':visible');
                $dropdown.toggle(!visible);
                if (!visible) {
                    Notifications.loadActivity();
                    $('#b-notif-dot').hide();
                }
            });

            $(document).off('click.bayyanoorNotifClose').on('click.bayyanoorNotifClose', function(e) {
                if (!$(e.target).closest('.b-top-user').length) {
                    $dropdown.hide();
                }
            });
        },

        loadActivity: function() {
            if (typeof bayyanoorAjax === 'undefined' || !bayyanoorAjax.logged_in) return;
            var $list = $('#b-notif-list');

            $.ajax({
                url: bayyanoorAjax.ajax_url,
                type: 'POST',
                data: { action: 'bayyanoor_get_activity', nonce: bayyanoorAjax.nonce },
                success: function(res) {
                    if (!res.success || !res.data || !res.data.length) {
                        $list.html('<li class="b-notif-empty">No recent activity.</li>');
                        return;
                    }
                    var html = '';
                    res.data.slice(0, 8).forEach(function(a) {
                        var xp = a.xp ? ' <strong style="color:#115e41">' + escHtml(a.xp) + '</strong>' : '';
                        html += '<li>' + escHtml(a.text) + xp + '<br><small style="color:#5f7068">' + escHtml(a.time) + '</small></li>';
                    });
                    $list.html(html);
                },
                error: function() {
                    $list.html('<li class="b-notif-empty">Could not load activity.</li>');
                }
            });
        },

        checkForNew: function() {
            if (typeof bayyanoorAjax === 'undefined' || !bayyanoorAjax.logged_in) return;
            $.ajax({
                url: bayyanoorAjax.ajax_url,
                type: 'POST',
                data: { action: 'bayyanoor_get_activity', nonce: bayyanoorAjax.nonce },
                success: function(res) {
                    if (res.success && res.data && res.data.length > 0) {
                        $('#b-notif-dot').show();
                    }
                }
            });
        }
    };

    // =========================================================
    // APP: Main Controller
    // =========================================================
    var App = {
        init: function() {
            Nav.init();
            Notifications.init();
            this.initComponents();

            // Check for notification dot on load
            setTimeout(function() {
                Notifications.checkForNew();
            }, 2000);

            // Log session start
            if (typeof console !== 'undefined') {
                console.log('[Bayyanoor] App initialized. Logged in:', bayyanoorAjax ? bayyanoorAjax.logged_in : false);
            }
        },
        
        initComponents: function() {
            Dashboard.init();
            AIChat.init();
            Auth.init();
        }
    };

    $(document).ready(function() {
        App.init();
    });

})(jQuery);
