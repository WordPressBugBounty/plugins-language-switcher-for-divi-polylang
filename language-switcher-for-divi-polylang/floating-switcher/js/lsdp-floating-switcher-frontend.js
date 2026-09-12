/**
 * Floating Language Switcher - Frontend JavaScript
 *
 * Handles the interactive behavior of the floating language switcher on the frontend.
 * Provides dropdown functionality, keyboard navigation, hover interactions, and
 * accessibility features for a smooth user experience.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/floating-switcher/js
 * @since      1.2.4
 */

(function () {
    'use strict';

    /**
     * FloatingSwitcher Class
     *
     * Manages the behavior and interactions of a single floating language switcher instance.
     * Handles dropdown toggle, keyboard navigation, mouse interactions, and dynamic width calculation.
     *
     * @class
     * @since 1.2.4
     */
    class FloatingSwitcher {
        /**
         * Constructor
         *
         * Initializes the floating switcher with necessary DOM elements and state.
         * Calculates initial width and sets up event listeners if dropdown exists.
         *
         * @since 1.2.4
         * @param {HTMLElement} element - The root switcher element
         */
        constructor(element) {
            this.switcher = element; // Root switcher element
            this.dropdown = element.querySelector('.lsdp-switcher-dropdown-list'); // Dropdown list container
            this.currentItem = element.querySelector('.lsdp-language-item__current[role="button"]'); // Active language button
            this.isOpen = false; // Tracks dropdown open/close state
            this.closeTimeout = null; // Timeout ID for delayed close on hover

            // Calculate and set fixed width to prevent layout shift
            this.setFixedWidth();

            // Initialize event listeners if required elements exist
            if (this.currentItem && this.dropdown) {
                this.init();
            }

            window.addEventListener('resize', () => {
                this.setFixedWidth();
            });
        }

        /**
         * Measure the natural rendered width of a language row.
         *
         * @since 1.2.5
         * @param {HTMLElement} item Language item element.
         * @return {number}
         */
        measureNaturalItemWidth(item) {
            const measurer = document.createElement('div');
            measurer.className = item.className;
            measurer.style.cssText = 'position:absolute;left:-9999px;top:-9999px;visibility:hidden;display:flex;align-items:center;white-space:nowrap;';

            const styles = window.getComputedStyle(item);
            measurer.style.padding = styles.padding;
            measurer.style.gap = styles.gap;
            measurer.style.fontSize = styles.fontSize;
            measurer.style.fontFamily = styles.fontFamily;
            measurer.style.fontWeight = styles.fontWeight;
            measurer.style.boxSizing = styles.boxSizing;
            measurer.style.minHeight = styles.minHeight;

            measurer.innerHTML = item.innerHTML;
            document.body.appendChild(measurer);

            const width = measurer.getBoundingClientRect().width;
            document.body.removeChild(measurer);

            return width;
        }

        /**
         * Set Fixed Width
         *
         * Locks switcher width to the widest language row so open and closed states
         * match the admin preview and long names stay fully visible.
         *
         * @since 1.2.4
         */
        setFixedWidth() {
            const mobileBreakpoint = (window.lsdpFloaterFrontend && window.lsdpFloaterFrontend.mobileBreakpoint) || 768;
            const isMobile = window.matchMedia(`(max-width: ${mobileBreakpoint - 1}px)`).matches;
            const widthVar = isMobile ? '--lsdp-mobile-width' : '--lsdp-desktop-width';
            const configuredWidth = getComputedStyle(this.switcher).getPropertyValue(widthVar).trim();

            this.switcher.style.removeProperty('min-width');

            if (configuredWidth && configuredWidth !== 'auto') {
                this.switcher.style.width = configuredWidth;
                return;
            }

            this.switcher.style.removeProperty('width');

            const items = this.switcher.querySelectorAll('.lsdp-language-item');
            let maxItemWidth = 0;

            items.forEach((item) => {
                maxItemWidth = Math.max(maxItemWidth, this.measureNaturalItemWidth(item));
            });

            if (maxItemWidth <= 0) {
                return;
            }

            const switcherStyles = window.getComputedStyle(this.switcher);
            const horizontalBorder =
                parseFloat(switcherStyles.borderLeftWidth || '0') +
                parseFloat(switcherStyles.borderRightWidth || '0');
            const horizontalPadding =
                parseFloat(switcherStyles.paddingLeft || '0') +
                parseFloat(switcherStyles.paddingRight || '0');

            this.switcher.style.width = Math.ceil(maxItemWidth + horizontalBorder + horizontalPadding) + 'px';

            const flags = this.switcher.querySelectorAll('.lsdp-flag-image');
            flags.forEach((flag) => {
                if (!flag.complete) {
                    flag.addEventListener('load', () => this.setFixedWidth(), { once: true });
                }
            });
        }

        /**
         * Initialize Event Listeners
         *
         * Sets up all event listeners for user interactions including:
         * - Click to toggle dropdown
         * - Hover interactions (desktop only)
         * - Keyboard navigation
         * - Outside click to close
         *
         * @since 1.2.4
         */
        init() {
            // Click handler: Toggle dropdown on click
            this.currentItem.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });

            // Hover handlers: Open on hover (desktop only, min-width: 768px)
            const mobileBreakpoint = (window.lsdpFloaterFrontend && window.lsdpFloaterFrontend.mobileBreakpoint) || 768;
            if (window.matchMedia(`(min-width: ${mobileBreakpoint}px)`).matches) {
                this.switcher.addEventListener('mouseenter', () => {
                    clearTimeout(this.closeTimeout); // Cancel any pending close
                    this.open();
                });

                this.switcher.addEventListener('mouseleave', () => {
                    // Delay close to prevent accidental closes during mouse movement
                    this.closeTimeout = setTimeout(() => this.close(), 200);
                });
            }

            // Keyboard navigation for the current language button
            this.currentItem.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    // Enter or Space: Toggle dropdown
                    e.preventDefault();
                    this.toggle();
                } else if (e.key === 'Escape') {
                    // Escape: Close dropdown
                    this.close();
                } else if (e.key === 'ArrowDown') {
                    // Arrow Down: Open dropdown and move focus to first item
                    e.preventDefault();
                    this.open();
                    this.focusFirstItem();
                }
            });

            // Keyboard navigation for dropdown items
            const items = this.dropdown.querySelectorAll('.lsdp-language-item');
            items.forEach((item, index) => {
                item.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowDown') {
                        // Arrow Down: Move to next item
                        e.preventDefault();
                        const nextItem = items[index + 1];
                        if (nextItem) nextItem.focus();
                    } else if (e.key === 'ArrowUp') {
                        // Arrow Up: Move to previous item or back to button
                        e.preventDefault();
                        if (index === 0) {
                            this.currentItem.focus();
                        } else {
                            items[index - 1].focus();
                        }
                    } else if (e.key === 'Escape') {
                        // Escape: Close dropdown and return focus to button
                        e.preventDefault();
                        this.close();
                        this.currentItem.focus();
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.switcher.contains(e.target)) {
                    this.close();
                }
            });
        }

        /**
         * Toggle Dropdown
         *
         * Toggles the dropdown between open and closed states.
         *
         * @since 1.2.4
         */
        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        /**
         * Open Dropdown
         *
         * Opens the language dropdown with proper accessibility attributes
         * and CSS classes for animations. Does nothing if already open.
         *
         * @since 1.2.4
         */
        open() {
            if (this.isOpen) return; // Already open, do nothing

            this.isOpen = true;
            this.switcher.classList.add('is-transitioning'); // Enable transition
            this.switcher.classList.add('is-open'); // Visual open state
            this.switcher.setAttribute('aria-expanded', 'true'); // Accessibility
            this.dropdown.removeAttribute('hidden'); // Make visible
            this.dropdown.removeAttribute('inert'); // Enable interaction

            // Remove transitioning class after animation completes (200ms)
            setTimeout(() => {
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        /**
         * Close Dropdown
         *
         * Closes the language dropdown with proper accessibility attributes
         * and CSS classes for animations. Does nothing if already closed.
         *
         * @since 1.2.4
         */
        close() {
            if (!this.isOpen) return; // Already closed, do nothing

            this.isOpen = false;
            this.switcher.classList.add('is-transitioning'); // Enable transition
            this.switcher.classList.remove('is-open'); // Remove visual open state
            this.switcher.setAttribute('aria-expanded', 'false'); // Accessibility

            // Hide dropdown after animation completes (200ms)
            setTimeout(() => {
                this.dropdown.setAttribute('hidden', ''); // Hide from screen readers
                this.dropdown.setAttribute('inert', ''); // Prevent interaction
                this.switcher.classList.remove('is-transitioning');
            }, 200);
        }

        /**
         * Focus First Item
         *
         * Moves keyboard focus to the first language item in the dropdown.
         * Used for keyboard navigation accessibility.
         *
         * @since 1.2.4
         */
        focusFirstItem() {
            const firstItem = this.dropdown.querySelector('.lsdp-language-item');
            if (firstItem) firstItem.focus();
        }
    }

    /**
     * Initialize Floating Switchers
     *
     * Finds all dropdown-type floating language switchers on the page
     * and initializes them with interactive behavior.
     *
     * @since 1.2.4
     */
    function initFloatingSwitchers() {
        // Find all dropdown-type floating switchers
        const switchers = document.querySelectorAll('.lsdp-floating-switcher.lsdp-ls-dropdown');

        // Initialize each switcher instance
        switchers.forEach(switcher => {
            new FloatingSwitcher(switcher);
        });
    }

    /**
     * Initialize on DOM Ready
     *
     * Ensures initialization happens after the DOM is fully loaded.
     * If DOM is already loaded, initialize immediately; otherwise wait for DOMContentLoaded.
     *
     * @since 1.2.4
     */
    if (document.readyState === 'loading') {
        // DOM still loading, wait for DOMContentLoaded event
        document.addEventListener('DOMContentLoaded', initFloatingSwitchers);
    } else {
        // DOM already loaded, initialize immediately
        initFloatingSwitchers();
    }

})();