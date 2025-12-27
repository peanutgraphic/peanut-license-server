/**
 * Peanut License Server Admin JavaScript
 */

(function($) {
    'use strict';

    // ==========================================================================
    // Dropdown Menu Toggle
    // ==========================================================================
    $(document).on('click', '.peanut-dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $dropdown = $(this).closest('.peanut-dropdown');
        var isOpen = $dropdown.hasClass('is-open');

        // Close all other dropdowns
        $('.peanut-dropdown').removeClass('is-open');

        // Toggle this dropdown
        if (!isOpen) {
            $dropdown.addClass('is-open');
        }
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.peanut-dropdown').length) {
            $('.peanut-dropdown').removeClass('is-open');
        }
    });

    // Close dropdown on escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.peanut-dropdown').removeClass('is-open');
        }
    });

    // ==========================================================================
    // Info Card Dismiss
    // ==========================================================================
    $(document).on('click', '.peanut-info-card-dismiss', function(e) {
        e.preventDefault();

        var $card = $(this).closest('.peanut-info-card');
        var cardId = $card.data('card-id');

        // Slide up and remove
        $card.slideUp(200, function() {
            $(this).remove();
        });

        // Save preference via AJAX if card has an ID
        if (cardId && typeof peanutLicenseAdmin !== 'undefined') {
            $.ajax({
                url: peanutLicenseAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'peanut_dismiss_info_card',
                    nonce: peanutLicenseAdmin.nonce,
                    card_id: cardId
                }
            });
        }
    });

    // ==========================================================================
    // Deactivate Site Button
    // ==========================================================================
    // Deactivate site button
    $(document).on('click', '.peanut-deactivate-site', function(e) {
        e.preventDefault();

        if (!confirm(peanutLicenseAdmin.strings.confirmDeactivate)) {
            return;
        }

        var $button = $(this);
        var $site = $button.closest('.peanut-site');
        var activationId = $button.data('id');

        $button.prop('disabled', true);

        $.ajax({
            url: peanutLicenseAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'peanut_deactivate_site',
                nonce: peanutLicenseAdmin.nonce,
                activation_id: activationId
            },
            success: function(response) {
                if (response.success) {
                    $site.slideUp(200, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || 'An error occurred');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred');
                $button.prop('disabled', false);
            }
        });
    });

    // Copy license key to clipboard
    $(document).on('click', '.column-license code', function() {
        var $code = $(this);
        var text = $code.text();

        navigator.clipboard.writeText(text).then(function() {
            var originalText = $code.text();
            $code.text('Copied!');
            setTimeout(function() {
                $code.text(originalText);
            }, 1500);
        });
    });

    // Add copy cursor to license codes
    $('.column-license code').css('cursor', 'pointer').attr('title', 'Click to copy');

    // ==========================================================================
    // License Map - Tree View Toggle
    // ==========================================================================
    // Toggle tree nodes
    $('.peanut-tree-toggle:not(.no-children)').on('click', function(e) {
        e.stopPropagation();
        var $node = $(this).closest('.peanut-tree-node');
        var $children = $node.next('.peanut-tree-children');

        if ($node.hasClass('is-expanded')) {
            $node.removeClass('is-expanded').addClass('is-collapsed');
            $children.slideUp(200);
        } else {
            $node.removeClass('is-collapsed').addClass('is-expanded');
            $children.slideDown(200);
        }
    });

    // Also toggle on node click (except links)
    $('.peanut-tree-node').on('click', function(e) {
        if ($(e.target).is('a') || $(e.target).closest('a').length) return;
        $(this).find('.peanut-tree-toggle:not(.no-children)').trigger('click');
    });

    // Expand all
    $('#peanut-map-expand-all').on('click', function() {
        $('.peanut-tree-node.is-collapsed').each(function() {
            $(this).removeClass('is-collapsed').addClass('is-expanded');
            $(this).next('.peanut-tree-children').slideDown(200);
        });
    });

    // Collapse all
    $('#peanut-map-collapse-all').on('click', function() {
        $('.peanut-tree-node.is-expanded').not('.product-node').each(function() {
            $(this).removeClass('is-expanded').addClass('is-collapsed');
            $(this).next('.peanut-tree-children').slideUp(200);
        });
    });

    // License Map Search
    var searchTimeout;
    $('#peanut-map-search').on('input', function() {
        clearTimeout(searchTimeout);
        var query = $(this).val().toLowerCase().trim();

        searchTimeout = setTimeout(function() {
            if (!query) {
                $('.peanut-tree-node').removeClass('search-match search-hidden');
                return;
            }

            // Search through licenses and sites
            $('.peanut-tree-license').each(function() {
                var $license = $(this);
                var email = $license.data('email').toLowerCase();
                var text = $license.text().toLowerCase();

                if (text.indexOf(query) !== -1 || email.indexOf(query) !== -1) {
                    $license.find('.license-node').addClass('search-match');
                    // Expand parents
                    $license.closest('.peanut-tree-tier').find('.tier-node').removeClass('is-collapsed').addClass('is-expanded')
                        .next('.peanut-tree-children').slideDown(200);
                } else {
                    $license.find('.license-node').removeClass('search-match');
                }
            });
        }, 200);
    });

    // Tier filter
    $('#peanut-map-filter-tier').on('change', function() {
        var tier = $(this).val();

        if (!tier) {
            $('.peanut-tree-tier').show();
        } else {
            $('.peanut-tree-tier').hide();
            $('.peanut-tree-tier[data-tier="' + tier + '"]').show();
        }
    });

    // ==========================================================================
    // GDPR Tools - Confirmation Toggle
    // ==========================================================================
    $('input[name="gdpr_action"]').on('change', function() {
        var val = $(this).val();
        if (val === 'anonymize' || val === 'delete') {
            $('.confirm-row').show();
        } else {
            $('.confirm-row').hide();
            $('input[name="confirm_action"]').prop('checked', false);
        }
    });

    // ==========================================================================
    // Settings Page - Recheck Plugin File
    // ==========================================================================
    $('#peanut-recheck-file').on('click', function() {
        var $btn = $(this);
        var product = $btn.data('product');
        $btn.prop('disabled', true).find('.dashicons').addClass('spin');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'peanut_recheck_plugin_file',
                nonce: typeof peanutLicenseAdmin !== 'undefined' ? peanutLicenseAdmin.recheckNonce : '',
                product: product
            },
            success: function(response) {
                if (response.success) {
                    $('#peanut-file-status').html(response.data.html);
                } else {
                    alert(response.data.message || 'Error checking file');
                }
            },
            error: function() {
                alert('Error checking file');
            },
            complete: function() {
                $btn.prop('disabled', false).find('.dashicons').removeClass('spin');
            }
        });
    });

})(jQuery);
