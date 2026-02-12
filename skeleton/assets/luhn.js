function LuhnCheck(cardNumber) {
    const cleaned = cardNumber.replace(/[\s-]/g, '');
    const reversed = cleaned.split('').reverse();
    let total = 0;

    for (let i = 0; i < reversed.length; i++) {
        let digit = parseInt(reversed[i]);

        if (i % 2 === 1) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }

        total += digit;
    }

    return total % 10 === 0;
}

export { LuhnCheck };