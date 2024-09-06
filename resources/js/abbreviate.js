const abbreviate = (number, precision = 1) => {
    const SI_SYMBOL = ["", "K", "M", "B", "T"];
    const tier = (Math.log10(Math.abs(number)) / 3) | 0;
    if (tier === 0) return number;
    const suffix = SI_SYMBOL[tier];
    const scale = Math.pow(10, tier * 3);
    const scaled = number / scale;
    return scaled.toFixed(precision) + suffix;
}

export { abbreviate };
