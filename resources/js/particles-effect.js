const particlesEffect = () => ({

    config: {
        // Color
        colorPalette: Math.random() * 30 + 330, // Pink
        colorSaturationInPercent: 70,
        colorLuminosityInPercent: 60,

        // Particles
        particleCount: 20,
        particleMinSizeInPx: 2,
        particleMaxSizeInPx: 10,

        // Animation
        animationMinDurationInMs: 500,
        animationMaxDurationInMs: 1000,
        animationParticleDelayInMs: 200,
        animationExplosionEffectRangeInPx: 60,
    },

    executeParticlesEffect(e) {
        // Only when button click is with mouse/finger
        if (e.clientX !== 0 || e.clientY !== 0) {

            for (let iParticle = 0; iParticle < this.config.particleCount; iParticle++) {
                // Coordinates of the mouse/finger
                let x = e.clientX,
                    y = e.clientY

                const particle = document.createElement('particleEffect')
                document.body.appendChild(particle)

                // Calculate a random size
                const size = Math.floor(Math.random() * this.config.particleMaxSizeInPx + this.config.particleMinSizeInPx)
                particle.style.width = `${size}px`
                particle.style.height = `${size}px`

                // Random color within the configured color palette
                particle.style.setProperty('--c', `hsl(
                    ${this.config.colorPalette},
                    ${this.config.colorSaturationInPercent}%,
                    ${this.config.colorLuminosityInPercent}%
                )`)
                particle.style.background = `
                    radial-gradient(at 70% 31%,var(--c) 29%,#0000 30%),
                    radial-gradient(at 30% 31%,var(--c) 29%,#0000 30%),
                    conic-gradient(from -45deg at 50% 84%,var(--c) 90deg,#0000 0)
                    bottom/100% 50% no-repeat
                `

                const particleAnimation = particle.animate([
                    // Origin position of the particle, in the center of the click
                    {
                        transform: `translate(-50%, -50%) translate(${x}px, ${y}px)`,
                        opacity: 1
                    },
                    // Final position
                    {
                        transform: `translate(
                            ${x + (Math.random() - 0.5) * 2 * this.config.animationExplosionEffectRangeInPx}px,
                            ${y + (Math.random() - 0.5) * 2 * this.config.animationExplosionEffectRangeInPx}px
                        )`,
                        opacity: 0
                    }
                ], {
                    duration: Math.random() * this.config.animationMaxDurationInMs + this.config.animationMinDurationInMs,
                    easing: 'cubic-bezier(0, .9, .57, 1)',
                    delay: Math.random() * this.config.animationParticleDelayInMs
                })

                particleAnimation.onfinish = () => {
                    particle.remove()
                }
            }
        }
    }
})

export { particlesEffect }
