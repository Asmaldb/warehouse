<!-- ================================================================ -->
<!-- COMPONENT: TransactionModal.vue                                  -->
<!-- File: src/components/TransactionModal.vue                        -->
<!-- مودال ثبت رویداد انبار (ورود / خروج / مرجوعی / تعدیل / ضایعات) -->
<!-- ================================================================ -->
<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center px-4"
       style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px)"
       @click.self="$emit('close')">
    <div class="card max-w-xl w-full border-dark-500 animate-slide-up max-h-[92vh] overflow-y-auto">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl"
               :style="{ background: selectedType?.bgColor || 'rgba(128,161,255,0.1)' }">
            {{ selectedType?.icon || '📋' }}
          </div>
          <div>
            <h2 class="text-lg font-bold text-white">ثبت رویداد انبار</h2>
            <p class="text-xs text-gray-500">{{ selectedType?.description || 'نوع رویداد را انتخاب کنید' }}</p>
          </div>
        </div>
        <button @click="$emit('close')"
                class="w-9 h-9 rounded-lg bg-dark-600 hover:bg-dark-500 text-gray-400
                       hover:text-white flex items-center justify-center transition-all">
          ✕
        </button>
      </div>

      <!-- Success Alert -->
      <div v-if="successMsg"
           class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl mb-5 text-sm">
        ✅ {{ successMsg }}
      </div>

      <!-- Error Alert -->
      <div v-if="serverError"
           class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-5 text-sm">
        ⚠️ {{ serverError }}
      </div>

      <div class="space-y-5">

        <!-- ─── نوع رویداد ─── -->
        <div>
          <label class="block text-gray-300 text-sm font-medium mb-3">نوع رویداد *</label>
          <div class="grid grid-cols-2 gap-2">
            <button v-for="type in transactionTypes" :key="type.value"
                    type="button"
                    @click="selectType(type)"
                    class="flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition-all text-right"
                    :class="form.transaction_type === type.value
                      ? 'border-primary bg-primary/10 text-white'
                      : 'border-dark-500 text-gray-400 hover:border-gray-500 hover:text-gray-300'">
              <span class="text-base">{{ type.icon }}</span>
              <div class="text-right">
                <div class="font-medium text-xs leading-tight">{{ type.label }}</div>
                <div class="text-xs opacity-60">{{ type.affect }} موجودی</div>
              </div>
            </button>
          </div>
          <p v-if="errors.transaction_type" class="text-red-400 text-xs mt-1">{{ errors.transaction_type }}</p>
        </div>

        <!-- ─── کالا ─── -->
        <div>
          <label class="block text-gray-300 text-sm font-medium mb-2">کالا *</label>
          <select v-model="form.product_id" class="input-dark"
                  :class="{ 'border-red-500': errors.product_id }">
            <option value="">--- کالا را انتخاب کنید ---</option>
            <option v-for="p in products" :key="p.id" :value="p.id">
              {{ p.name }} ({{ p.code }}) — موجودی: {{ p.quantity }}
            </option>
          </select>
          <p v-if="errors.product_id" class="text-red-400 text-xs mt-1">{{ errors.product_id }}</p>
          <!-- نمایش موجودی فعلی -->
          <div v-if="selectedProduct"
               class="mt-2 text-xs text-gray-500 flex items-center gap-1">
            <span>موجودی فعلی:</span>
            <span class="font-bold"
                  :class="selectedProduct.quantity < 5 ? 'text-red-400' : 'text-green-400'">
              {{ selectedProduct.quantity }} عدد
            </span>
          </div>
        </div>

        <!-- ─── مقدار و قیمت ─── -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-gray-300 text-sm font-medium mb-2">
              مقدار *
              <span v-if="selectedType" class="text-xs font-normal"
                    :class="selectedType.affect === '+' ? 'text-green-400' : selectedType.affect === '−' ? 'text-red-400' : 'text-yellow-400'">
                ({{ selectedType.affect }})
              </span>
            </label>
            <input v-model.number="form.quantity" type="number" min="0.01" step="0.01"
                   placeholder="مثال: 10"
                   class="input-dark" :class="{ 'border-red-500': errors.quantity }" />
            <p v-if="errors.quantity" class="text-red-400 text-xs mt-1">{{ errors.quantity }}</p>
          </div>
          <div>
            <label class="block text-gray-300 text-sm font-medium mb-2">قیمت واحد (تومان)</label>
            <input v-model.number="form.unit_price" type="number" min="0"
                   :placeholder="selectedProduct ? selectedProduct.price : '0'"
                   class="input-dark" />
            <p class="text-xs text-gray-600 mt-1">پیش‌فرض: قیمت ثبت‌شده کالا</p>
          </div>
        </div>

        <!-- محاسبه ارزش کل -->
        <div v-if="form.quantity && form.unit_price"
             class="bg-primary/5 border border-primary/20 rounded-xl px-4 py-3 text-sm">
          <span class="text-gray-400">ارزش کل این رویداد: </span>
          <span class="text-primary font-bold text-base">{{ formatPrice(form.quantity * form.unit_price) }}</span>
        </div>

        <!-- ─── شماره سند ─── -->
        <div>
          <label class="block text-gray-300 text-sm font-medium mb-2">شماره سند / حواله</label>
          <input v-model="form.reference_number" type="text"
                 placeholder="مثال: INV-2024-001"
                 class="input-dark font-mono" />
        </div>

        <!-- ─── نام طرف مقابل (اگر لازم بود) ─── -->
        <div v-if="selectedType?.needsParty">
          <label class="block text-gray-300 text-sm font-medium mb-2">
            {{ selectedType.partyLabel }}
          </label>
          <input v-model="form.party_name" type="text"
                 :placeholder="'مثال: ' + selectedType.partyLabel"
                 class="input-dark" />
        </div>

        <!-- ─── توضیحات ─── -->
        <div>
          <label class="block text-gray-300 text-sm font-medium mb-2">توضیحات</label>
          <textarea v-model="form.description" rows="2"
                    placeholder="توضیحات اضافی..."
                    class="input-dark resize-none"></textarea>
        </div>

        <!-- ─── دکمه‌ها ─── -->
        <div class="flex gap-3 pt-2">
          <button type="button" @click="$emit('close')"
                  class="flex-1 border border-dark-500 text-gray-300 py-3 rounded-xl hover:border-gray-400 transition-all">
            انصراف
          </button>
          <button type="button" @click="handleSubmit" class="flex-1 btn-primary py-3"
                  :disabled="store.loading">
            <span v-if="store.loading" class="flex items-center justify-center gap-2">
              <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              در حال ثبت...
            </span>
            <span v-else>ثبت رویداد</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useTransactionStore, TRANSACTION_TYPES } from '@/store/transactions'

const props = defineProps({
  products: { type: Array, default: () => [] },
  preSelectedProductId: { type: Number, default: null },
})
const emit = defineEmits(['close', 'saved'])

const store = useTransactionStore()

const transactionTypes = TRANSACTION_TYPES

const form = reactive({
  transaction_type: '',
  product_id:       props.preSelectedProductId || '',
  quantity:         null,
  unit_price:       null,
  reference_number: '',
  party_name:       '',
  description:      '',
})
const errors = reactive({
  transaction_type: '',
  product_id: '',
  quantity: '',
})
const successMsg  = ref('')
const serverError = ref('')

const selectedType = computed(() =>
  TRANSACTION_TYPES.find(t => t.value === form.transaction_type)
)

const selectedProduct = computed(() =>
  props.products.find(p => p.id === form.product_id)
)

const selectType = (type) => {
  form.transaction_type = type.value
  errors.transaction_type = ''
}

const validate = () => {
  errors.transaction_type = ''
  errors.product_id = ''
  errors.quantity = ''
  let ok = true
  if (!form.transaction_type) { errors.transaction_type = 'نوع رویداد الزامی است'; ok = false }
  if (!form.product_id) { errors.product_id = 'انتخاب کالا الزامی است'; ok = false }
  if (!form.quantity || form.quantity <= 0) { errors.quantity = 'مقدار باید بیشتر از صفر باشد'; ok = false }
  return ok
}

const formatPrice = (n) => new Intl.NumberFormat('fa-IR').format(Math.round(n)) + ' تومان'

const handleSubmit = async () => {
  if (!validate()) return
  serverError.value = ''

  const payload = {
    product_id:       form.product_id,
    transaction_type: form.transaction_type,
    quantity:         form.quantity,
    unit_price:       form.unit_price || undefined,
    reference_number: form.reference_number || undefined,
    party_name:       form.party_name || undefined,
    description:      form.description || undefined,
  }

  const result = await store.createTransaction(payload)

  if (result.success) {
    successMsg.value = `رویداد "${selectedType.value?.label}" با موفقیت ثبت شد. موجودی جدید: ${result.newBalance}`
    setTimeout(() => { successMsg.value = ''; emit('saved', result.transaction) }, 1800)
  } else {
    if (result.errors) Object.assign(errors, result.errors)
    if (result.message) serverError.value = result.message
  }
}
</script>
