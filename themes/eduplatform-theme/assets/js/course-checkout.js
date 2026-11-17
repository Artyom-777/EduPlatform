
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.course-enrollment-form');

    if (!form) {
        return;
    }

    let checkout = null;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('[name="edu_enroll_submit"]');

        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;

        const originalText = button.textContent;
        button.textContent = 'Loading checkout...';

        try {
            if (
                typeof Stripe !== 'function' ||
                !window.eduPlatformCheckout ||
                !eduPlatformCheckout.publishableKey
            ) {
                throw new Error('Stripe configuration is missing.');
            }

            const formData = new FormData(form);
            const courseId = formData.get('course_id');

            const response = await fetch(
                eduPlatformCheckout.restUrl + 'checkout-session',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': eduPlatformCheckout.nonce
                    },
                    body: JSON.stringify({
                        course_id: courseId
                    })
                }
            );

            const data = await response.json();

            if (!response.ok) {
                throw new Error(
                    data.message || 'Unable to create checkout session.'
                );
            }

            if (!data.client_secret) {
                throw new Error('Checkout client secret was not returned.');
            }

            const stripe = Stripe(
                eduPlatformCheckout.publishableKey
            );

            checkout = await stripe.initEmbeddedCheckout({
                fetchClientSecret: async () => data.client_secret
            });

            let container = document.getElementById(
                'edu-platform-checkout'
            );

            if (!container) {
                container = document.createElement('div');
                container.id = 'edu-platform-checkout';
                form.insertAdjacentElement('afterend', container);
            }

            form.hidden = true;

            checkout.mount('#edu-platform-checkout');

        } catch (error) {
            console.error('EduPlatform Checkout:', error);

            let errorElement = document.getElementById(
                'edu-platform-checkout-error'
            );

            if (!errorElement) {
                errorElement = document.createElement('p');
                errorElement.id = 'edu-platform-checkout-error';
                errorElement.setAttribute('role', 'alert');
                form.insertAdjacentElement('afterend', errorElement);
            }

            errorElement.textContent = error.message;

            button.disabled = false;
            button.textContent = originalText;
        }
    });
});
