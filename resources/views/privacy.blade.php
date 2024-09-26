<x-app-layout>
    <div class="mx-auto my-16 max-w-7xl px-6 lg:px-8">
        <a
            href="{{ url()->previous() === request()->url() ? '/' : url()->previous() }}"
            class="-mt-10 mb-12 flex items-center dark:text-slate-400 text-slate-600 hover:underline z-50 relative"
            wire:navigate
        >
            <x-icons.chevron-left class="size-4" />
            <span>Back</span>
        </a>

        <div class="mt-6">
            <div class="prose prose-slate dark:prose-invert mx-auto max-w-4xl">
                <h1>Privacy Policy</h1>
                <p><strong>Last Updated: 19 Feb 2024</strong></p>

                <p>
                    Welcome to Pinkary ("we," "us," "our"). We are committed to protecting your personal information and your right to privacy. If you
                    have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please
                    contact us at team@pinkary.com.
                </p>

                <h2>1. Information We Collect</h2>
                <p>
                    We may collect personal information that you voluntarily provide to us when you register on the Services, express an interest in
                    obtaining information about us or our products and services, participate in activities on the Services, or otherwise when you
                    contact us.
                </p>
                <p>
                    The personal information that we collect depends on the context of your interactions with us and the Services, the choices you
                    make, and the products and features you use. The personal information we collect may include the following:
                </p>
                <ul>
                    <li>Name</li>
                    <li>Email address</li>
                    <li>Account passwords</li>
                    <li>Social media account information</li>
                    <li>Payment information</li>
                    <li>Other personal information you choose to provide</li>
                </ul>
                <p>
                    However, we may also collect additional information when delivering our Services to you to ensure necessary and optimal
                    performance. These methods of collection may not be as obvious to you, so we wanted to highlight and explain them below:
                </p>
                <ul>
                    <li>Log and Usage Data</li>
                    <li>Location Information</li>
                </ul>

                <h2>2. How We Use Your Information</h2>
                <p>We use personal information collected via our Services for a variety of business purposes described below:</p>
                <ul>
                    <li>To facilitate account creation and the login process.</li>
                    <li>To manage user accounts.</li>
                    <li>To send administrative information to you.</li>
                    <li>To fulfill and manage your orders.</li>
                    <li>To respond to user inquiries and offer support to users.</li>
                    <li>To comply with our legal obligations.</li>
                    <li>To respond to legal requests and prevent harm.</li>
                    <li>
                        For other business purposes such as data analysis, identifying usage trends, determining the effectiveness of our promotional
                        campaigns, and to evaluate and improve our Services, products, marketing, and your experience.
                    </li>
                </ul>

                <h2>3. Sharing Your Information</h2>
                <p>We may share or transfer your information in the following situations:</p>
                <ul>
                    <li>
                        With service providers: We may share your information with third-party service providers, contractors, or agents who perform
                        services for us or on our behalf and require access to such information to do that work.
                    </li>
                    <li>
                        For business transfers: We may share or transfer your information in connection with, or during negotiations of, any merger,
                        sale of company assets, financing, or acquisition of all or a portion of our business to another company.
                    </li>
                    <li>With your consent: We may disclose your personal information for any other purpose with your consent.</li>
                </ul>

                <h2>4. Data Retention</h2>
                <p>
                    We will retain your personal information only for as long as is necessary for the purposes set out in this Privacy Policy, unless
                    a longer retention period is required or permitted by law (such as tax, accounting, or other legal requirements).
                </p>

                <h2>5. Data Protection Rights</h2>
                <p>
                    Under the General Data Protection Regulation (GDPR), you have certain rights regarding your personal information. These include
                    the right to request access, correction, deletion, or restriction of your personal information, and the right to data portability.
                    If you wish to exercise any of these rights, please contact us.
                </p>

                <h2>6. Changes to This Privacy Policy</h2>
                <p>
                    We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this
                    page and updating the "Last Updated" date.
                </p>

                <h2>7. Contact Us</h2>
                <p>
                    If you have questions or comments about this policy, you may email us at
                    <a href="mailto:team@pinkary.com">team@pinkary.com</a>
                    .
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
