const PASSWORD_MINIMUM_LENGTH = 8;
const BREACH_CHECK_DELAY = 400;

/**
 * Manage live password validation on the registration form.
 *
 * The Pwned Passwords range API only receives the first five characters of
 * the password's SHA-1 hash. The remaining hash characters stay in the
 * browser and are compared with the returned range locally.
 */
export function passwordRegistration() {
    return {
        password: "",
        passwordConfirmation: "",
        passwordFocused: false,
        breachCheckStatus: "idle",
        breachCheckTimeout: null,
        breachRequestId: 0,

        init() {
            this.$watch("password", (password) =>
                this.queueBreachCheck(password),
            );
        },

        canSubmit() {
            return (
                this.password.length >= PASSWORD_MINIMUM_LENGTH &&
                this.password === this.passwordConfirmation &&
                this.breachCheckStatus !== "checking" &&
                this.breachCheckStatus !== "compromised"
            );
        },

        passwordStrength() {
            if (this.password.length < PASSWORD_MINIMUM_LENGTH) {
                return {
                    color: "bg-red-500",
                    label: "Too short",
                    score: 1,
                    textColor: "text-red-600 dark:text-red-400",
                };
            }

            let score = 1;

            if (/[A-Z]/.test(this.password)) score++;
            if (/\d/.test(this.password)) score++;
            if (/[^A-Za-z0-9]/.test(this.password)) score++;

            return [
                {
                    color: "bg-orange-500",
                    label: "Weak",
                    score: 1,
                    textColor: "text-orange-600 dark:text-orange-400",
                },
                {
                    color: "bg-amber-500",
                    label: "Fair",
                    score: 2,
                    textColor: "text-amber-600 dark:text-amber-400",
                },
                {
                    color: "bg-lime-500",
                    label: "Good",
                    score: 3,
                    textColor: "text-lime-600 dark:text-lime-400",
                },
                {
                    color: "bg-emerald-500",
                    label: "Strong",
                    score: 4,
                    textColor: "text-emerald-600 dark:text-emerald-400",
                },
            ][score - 1];
        },

        queueBreachCheck(password) {
            clearTimeout(this.breachCheckTimeout);

            if (password.length === 0) {
                this.breachCheckStatus = "idle";

                return;
            }

            this.breachCheckStatus = "checking";
            const requestId = ++this.breachRequestId;

            this.breachCheckTimeout = setTimeout(
                () => this.checkForBreach(password, requestId),
                BREACH_CHECK_DELAY,
            );
        },

        async checkForBreach(password, requestId) {
            try {
                const passwordHash = await this.sha1(password);
                const prefix = passwordHash.slice(0, 5);
                const suffix = passwordHash.slice(5);
                const response = await fetch(
                    `https://api.pwnedpasswords.com/range/${prefix}`,
                    {
                        cache: "no-store",
                        headers: { "Add-Padding": "true" },
                    },
                );

                if (!response.ok) {
                    throw new Error("The breach check failed.");
                }

                const leaked = (await response.text())
                    .split("\r\n")
                    .some((entry) => entry.split(":")[0] === suffix);

                if (requestId === this.breachRequestId) {
                    this.breachCheckStatus = leaked
                        ? "compromised"
                        : "uncompromised";
                }
            } catch {
                if (requestId === this.breachRequestId) {
                    this.breachCheckStatus = "unavailable";
                }
            }
        },

        async sha1(value) {
            const bytes = new TextEncoder().encode(value);
            const hash = await crypto.subtle.digest("SHA-1", bytes);

            return Array.from(new Uint8Array(hash), (byte) =>
                byte.toString(16).padStart(2, "0"),
            )
                .join("")
                .toUpperCase();
        },
    };
}
