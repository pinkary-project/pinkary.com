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
                <h1>Terms of Service</h1>
                <p><strong>Last Updated: 21 July 2024</strong></p>

                <p>
                    Welcome to Pinkary, a digital platform designed to streamline your online presence by allowing users to create a personalized and
                    easily manageable link hub. By accessing or using our website, mobile applications, and services (collectively, the “Services”),
                    you agree to be bound by these Terms of Service (“Terms”).
                </p>

                <h2>1. Acceptance of Terms</h2>
                <p>
                    By creating an account, accessing, or using the Services, you signify your agreement to these Terms. If you do not agree to these
                    Terms, you may not access or use the Services. Pinkary reserves the right to modify or replace these Terms at any time at its sole
                    discretion. Your continued use of the Services after any changes signifies your acceptance of the new Terms.
                </p>

                <h2>2. Use of Services</h2>
                <p>
                    <strong>a. Eligibility:</strong>
                    You must be at least 16 years old to use the Services, in compliance with Portuguese law. By agreeing to these Terms, you
                    represent and warrant that you are of legal age to form a binding contract.
                </p>
                <p>
                    <strong>b. Pinkary Account:</strong>
                    You may need to register an account to access certain features of the Services. You are responsible for maintaining the
                    confidentiality of your account and password.
                </p>
                <p>
                    <strong>c. Acceptable Use:</strong>
                    You agree not to use the Services for any unlawful purposes or in ways that could damage, disable, overburden, or impair the
                    Services or interfere with any other party's use and enjoyment of the Services.
                </p>

                <h2>3. Content</h2>

                <p>
                    <strong>a. User Content:</strong>
                    You are responsible for the content you provide, including compliance with applicable laws, rules, and regulations under Portuguese
                    and EU law. You retain all rights in, and are solely responsible for, the User Content you post to Pinkary.
                    Pinkary does not endorse, verify, or assume responsibility for any User Content.
                </p>
                <p>
                    <strong>b. Rights Granted by You:</strong>
                    By posting content to the Services, you grant Pinkary a non-exclusive, worldwide, royalty-free, sublicensable, and transferable
                    license to use, reproduce, distribute, prepare derivative works of, display, and perform the content in connection with the Services.
                    This license ends when you delete the content or your account, unless the content has been shared with others and they have not deleted it.
                </p>

                <p>
                    <strong>c. Permissions and Ownership:</strong>
                    You must own or have obtained all necessary rights and permissions to any content you upload or use in your user content on this site.
                    By posting content, you affirm that you either own the copyright to the content or have obtained all necessary permissions from the
                    copyright owner(s) to use and share the content on Pinkary.
                </p>

                <p>
                    <strong>d. Prohibited Content:</strong>
                    You may not post content that is illegal, hateful, obscene, threatening, defamatory, vulgar, libelous, trade secret, invasive of privacy,
                    infringing of intellectual property rights, or otherwise injurious to third parties or objectionable.
                    Pinkary reserves the right to remove any content that violates these Terms or is otherwise inappropriate.
                </p>

                <p>
                    <strong>e. Reporting Infringements:</strong>
                    If you believe that any content on Pinkary infringes upon your copyright, please contact us with a detailed notice of the alleged infringement,
                    and we will take appropriate action.
                </p>

                <h2>4. Intellectual Property</h2>
                <p>
                    The Services and original content (excluding User Content), features, and functionality are and will remain the exclusive property
                    of Pinkary and its licensors.
                </p>

                <h2>5. Termination</h2>
                <p>
                    Pinkary may terminate or suspend your access to the Services immediately, without prior notice or liability, for any reason,
                    including if you breach the Terms.
                </p>

                <h2>6. Disclaimers and Limitations of Liability</h2>
                <p>
                    The Services are provided on an “as is” and “as available” basis. Pinkary expressly disclaims all warranties of any kind, whether
                    express or implied, including, but not limited to, the warranties of merchantability, fitness for a particular purpose, and
                    non-infringement. Pinkary will not be liable for any indirect, incidental, special, consequential, or punitive damages resulting
                    from your use of the Services.
                </p>

                <h2>7. Governing Law</h2>
                <p>
                    These Terms shall be governed and construed in accordance with the laws of Portugal and the regulations of the European Union,
                    without regard to its conflict of law provisions.
                </p>

                <h2>8. Changes</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time.</p>

                <h2>9. Contact Us</h2>
                <p>
                    If you have any questions about these Terms, please contact us at
                    <a href="mailto:team@pinkary.com">team@pinkary.com</a>
                    .
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
