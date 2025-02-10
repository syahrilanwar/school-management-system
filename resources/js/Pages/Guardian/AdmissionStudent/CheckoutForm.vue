<script setup>
import DefaultButton from '@/Components/DefaultButton.vue';
import midtrans from '@/Configs/midtrans';
import fieldValidation from '@/Helpers/fieldValidation';
import axios from 'axios';
import { ElNotification } from 'element-plus';
midtrans.init();
</script>
<script>
export default {
    props: {
        propertyModal: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            process: false,
            loaded: true,
            isValid: false,
            form: {
                name: null,
                birth_date: null,
                school: null,
                school_grade: null,
            },
            field: {
                name: {
                    label: 'Nama Lengkap Siswa',
                    rules: [fieldValidation.isRequired('Nama Lengkap Siswa')],
                    error: null,
                },
                birth_date: {
                    label: 'Tanggal Lahir',
                    rules: [fieldValidation.isRequired('Tanggal Lahir')],
                    error: null,
                },
                school: {
                    label: 'Sekolah',
                    rules: [fieldValidation.isRequired('Sekolah')],
                    error: null,
                    disabled: false,
                    loading: false,
                    options: [],
                },
                school_grade: {
                    label: 'Kelas',
                    rules: [fieldValidation.isRequired('Kelas')],
                    error: null,
                    disabled: true,
                    loading: false,
                    options: [],
                },
            },
            product: this.propertyModal?.data?.product,
        };
    },
    watch: {
        'form.school'() {
            if (this.form.school) {
                this.form.school_grade = null;
                this.field.school_grade.disabled = false;
                this.field.school_grade.options = this.form.school.grades;
            } else {
                this.form.school_grade = null;
                this.field.school_grade.disabled = true;
                this.field.school_grade.options = [];
            }
        },
    },
    methods: {
        optionSchool(search) {
            this.field.school.loading = true;
            axios
                .get(
                    route('guardian.admissionStudent.optionSchool', {
                        search: search,
                    }),
                )
                .then((response) => {
                    this.field.school.options = response.data;
                    this.field.school.loading = false;
                })
                .catch((error) => {
                    console.log(error);
                    this.field.school.loading = false;
                });
        },
        submit() {
            this.$refs['checkoutForm'].validate((valid) => {
                if (valid) {
                    this.process = true;
                    let requestPayload = JSON.parse(JSON.stringify(this.form));
                    requestPayload.school = requestPayload.school?.uuid;
                    requestPayload.school_grade = requestPayload.school_grade?.uuid;

                    axios
                        .post(route('guardian.admissionStudent.checkout'), requestPayload, {
                            headers: { 'Content-Type': 'application/json' },
                        })
                        .then((response) => {
                            let transaction_payment = response.data.transaction_payment;
                            let snap_token = response.data.snap_token;

                            try {
                                window.snap.pay(snap_token, {
                                    onSuccess: (gateway_response) => {
                                        this.processPayment(transaction_payment, gateway_response);

                                        ElNotification({
                                            title: 'Pembayaran Berhasil',
                                            message: 'Terima kasih! Pembayaran Anda telah berhasil diproses.',
                                            type: 'success',
                                        });

                                        this.process = false;
                                    },
                                    onPending: (gateway_response) => {
                                        this.processPayment(transaction_payment, gateway_response);

                                        ElNotification({
                                            title: 'Menunggu Pembayaran',
                                            message:
                                                'Pembayaran Anda telah dimasukkan ke dalam tagihan. Silakan selesaikan transaksi segera.',
                                            type: 'warning',
                                        });

                                        this.process = false;
                                    },
                                    onError: (gateway_response) => {
                                        this.processPayment(transaction_payment, gateway_response);

                                        ElNotification({
                                            title: 'Pembayaran Gagal',
                                            message: 'Terjadi kesalahan dalam proses pembayaran. Silakan coba lagi.',
                                            type: 'error',
                                        });

                                        this.process = false;
                                    },
                                    onClose: (gateway_response) => {
                                        console.log(gateway_response);

                                        ElNotification({
                                            title: 'Transaksi Ditutup',
                                            message: 'Anda telah menutup pembayaran sebelum menyelesaikannya.',
                                            type: 'info',
                                        });

                                        this.process = false;
                                    },
                                });
                            } catch (error) {
                                this.process = true;

                                console.error(error);
                            }
                        })
                        .catch((error) => {
                            ElNotification({
                                title: 'Error',
                                message: 'Terjadi kesalahan.',
                                type: 'error',
                            });

                            if (error.response?.data?.errors) {
                                for (let field in error.response.data.errors) {
                                    this.field[field].error = error.response.data.errors[field];
                                    this.$refs['checkoutForm'].validateField(field);
                                }
                            }
                        })
                        .finally(() => {
                            this.process = false;
                        });
                }
            });
        },
        processPayment(transaction_payment, gateway_response) {
            this.process = true;

            axios
                .post(
                    route('guardian.transactionPayment.processPayment'),
                    {
                        transaction_payment: transaction_payment.uuid,
                        gateway_response: gateway_response,
                    },
                    {
                        headers: { 'Content-Type': 'application/json' },
                    },
                )
                .then((response) => {
                    console.log(response.data.status);
                    if (response.data.status) {
                        setTimeout(() => {
                            this.close();
                            this.$inertia.reload();
                        }, 2000);
                    }
                })
                .catch((error) => {
                    ElNotification({
                        title: 'Error',
                        message: error,
                        type: 'error',
                    });
                })
                .finally(() => {
                    this.process = false;
                });
        },
        close() {
            this.$emit('close');
        },
    },
};
</script>
<template>
    <div class="space-y-6 p-5">
        <h2 class="border-b pb-4 text-lg font-bold text-gray-900">
            {{ propertyModal?.title }}
        </h2>
        <div class="px-2">
            <el-form v-if="loaded" ref="checkoutForm" label-position="top" :model="form" :disabled="process">
                <el-form-item
                    class="font-bold"
                    :label="field.name.label"
                    :rules="field.name.rules"
                    :error="field.name.error"
                    prop="name"
                >
                    <el-input v-model="form.name" autocomplete="off" />
                </el-form-item>
                <el-form-item
                    class="font-bold"
                    :label="field.birth_date.label"
                    :rules="field.birth_date.rules"
                    :error="field.birth_date.error"
                    prop="birth_date"
                >
                    <el-date-picker
                        v-model="form.birth_date"
                        type="date"
                        format="DD-MM-YYYY"
                        value-format="YYYY-MM-DD"
                    />
                </el-form-item>
                <el-form-item
                    class="font-bold"
                    :label="field.school.label"
                    :rules="field.school.rules"
                    :error="field.school.error"
                    prop="school"
                >
                    <el-select
                        v-model="form.school"
                        :placeholder="`Pilih ${field.school.label}`"
                        loading-text="..."
                        no-match-text="Data tidak ditemukan"
                        no-data-text="Tidak ada data"
                        :disabled="field.school.disabled"
                        :remote-method="optionSchool"
                        value-key="uuid"
                        remote
                        filterable
                        reserve-keyword
                        clearable
                        autocomplete="off"
                    >
                        <el-option
                            v-for="option in field.school.options"
                            :key="option.uuid"
                            :label="option.area.name"
                            :value="option"
                        />
                    </el-select>
                </el-form-item>
                <el-form-item
                    class="font-bold"
                    :label="field.school_grade.label"
                    :rules="field.school_grade.rules"
                    :error="field.school_grade.error"
                    prop="school_grade"
                >
                    <el-select
                        v-model="form.school_grade"
                        :placeholder="`Pilih ${field.school_grade.label}`"
                        loading-text="..."
                        no-match-text="Data tidak ditemukan"
                        no-data-text="Tidak ada data"
                        :disabled="field.school_grade.disabled"
                        value-key="uuid"
                        clearable
                        autocomplete="off"
                    >
                        <el-option
                            v-for="option in field.school_grade.options"
                            :key="option.uuid"
                            :label="option.title"
                            :value="option"
                        />
                    </el-select>
                </el-form-item>
            </el-form>
        </div>
        <div
            class="space-y-4 rounded-lg border border-gray-100 bg-gray-50 p-6 text-sm dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="space-y-4">
                <dl class="flex items-center justify-between gap-4">
                    <dt class="font-normal text-gray-500 dark:text-gray-400">Harga</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">IDR {{ product.price }}</dd>
                </dl>
                <dl class="flex items-center justify-between gap-4">
                    <dt class="font-normal text-gray-500 dark:text-gray-400">Diskon</dt>
                    <dd class="font-medium text-green-500 dark:text-white">-</dd>
                </dl>
            </div>
            <dl
                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 text-base dark:border-gray-700"
            >
                <dt class="font-bold text-gray-900 dark:text-white">Total</dt>
                <dd class="font-bold text-gray-900 dark:text-white">IDR {{ product.price }}</dd>
            </dl>
        </div>
        <div class="flex justify-end space-x-3">
            <DefaultButton class="w-full" type="light" @click="close" :disabled="process"> Batal </DefaultButton>

            <DefaultButton class="w-full" type="default" @click="submit" :disabled="process">
                Beli Formulir
            </DefaultButton>
        </div>
    </div>
</template>
