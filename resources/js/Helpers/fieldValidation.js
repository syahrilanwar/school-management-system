export default {
    // Format aturan untuk Element Plus
    isRequired(field = 'Input') {
        return {
            required: true,
            message: `Kolom ${field} wajib diisi.`,
        };
    },
    isNumber(field = 'Input') {
        return {
            type: 'number',
            message: `Kolom ${field} harus berupa angka.`,
        };
    },
    isEmail(field = 'Input') {
        return {
            type: 'email',
            message: `Kolom ${field} harus berupa alamat email yang valid.`,
        };
    },
    isLength(min, max) {
        return {
            min: min,
            max: max,
            message: `Karakter harus antara ${min} hingga ${max}.`,
            trigger: 'change',
        };
    },
};
