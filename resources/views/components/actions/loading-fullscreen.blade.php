<style>
    @keyframes pulse-shadow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(var(--p), 0.4);
        }

        50% {
            box-shadow: 0 0 0 15px rgba(var(--p), 0);
        }
    }

    .loading-pulse {
        animation: pulse-shadow 1.5s infinite;
    }
</style>

<div id="fullscreen-loader"
    class="fixed inset-0 bg-base-300/80 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity duration-300 opacity-0 pointer-events-none">
    <div class="bg-base-100 rounded-xl shadow-xl p-8 max-w-md w-full mx-4 flex flex-col items-center">
        <div class="loading loading-spinner loading-lg text-primary mb-4"></div>
        <p id="loading-message" class="text-center text-base-content font-medium text-lg"></p>
    </div>
</div>

{{-- @push('scripts') --}}
    <script defer>
        /**
         * Show or hide the fullscreen loading overlay
         * @param {boolean} isLoading - Whether to show or hide the loading screen
         * @param {string} message - Optional message to display (only used when isLoading is true)
         */
        window.setLoading = (isLoading, message = "") => {
            const loader = document.getElementById("fullscreen-loader")
            const messageEl = document.getElementById("loading-message")

            if (isLoading) {
                // Set the message
                messageEl.textContent = message

                // Show the loader
                loader.classList.remove("opacity-0", "pointer-events-none")
                loader.classList.add("opacity-100")

                // Prevent scrolling on the body
                document.body.style.overflow = "hidden"
            } else {
                // Hide the loader
                loader.classList.remove("opacity-100")
                loader.classList.add("opacity-0", "pointer-events-none")

                // Re-enable scrolling
                document.body.style.overflow = ""

                // Clear the message after animation completes
                setTimeout(() => {
                    messageEl.textContent = ""
                }, 300)
            }
        }

        //* Example of how to use the loading screen in your application:
        //
        // // Show loading screen
        // setLoading(true, 'Processing your request...');
        //
        // // Simulate an API call
        // setTimeout(() => {
        // // Hide loading screen when done
        // setLoading(false);
        // }, 2000);
    </script>
{{-- @endpush --}}
