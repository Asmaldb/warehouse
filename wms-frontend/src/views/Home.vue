<template>
  <div>
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
      <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary-400/8 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/3 w-64 h-64 bg-primary-400/5 rounded-full blur-3xl animate-pulse-slow" style="animation-delay:1.5s"></div>
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image:linear-gradient(rgba(128,161,255,0.4) 1px,transparent 1px),linear-gradient(90deg,rgba(128,161,255,0.4) 1px,transparent 1px);background-size:60px 60px">
        </div>
      </div>

      <div class="relative z-10 max-w-5xl mx-auto px-6 text-center">
        <!-- لوگو -->
        <div class="flex justify-center mb-8">
          <img src="@/assets/logo.png" alt="SKY Warehouse" class="h-28 w-28 object-contain drop-shadow-2xl" />
        </div>

        <div class="inline-flex items-center gap-2 bg-primary-400/10 border border-primary-400/30
                    text-primary-400 px-4 py-2 rounded-full text-sm font-medium mb-8">
          <span class="w-2 h-2 bg-primary-400 rounded-full animate-pulse"></span>
          سیستم مدیریت انبار SKY Warehouse
        </div>

        <h1 class="text-5xl md:text-7xl font-black mb-6 leading-tight">
          <span class="text-white">انبار خود را</span><br/>
          <span class="bg-gradient-to-l from-primary-400 to-white bg-clip-text text-transparent">
            هوشمندانه
          </span>
          <span class="text-white"> مدیریت کنید</span>
        </h1>

        <p class="text-gray-400 text-xl md:text-2xl mb-12 max-w-3xl mx-auto leading-relaxed">
          با SKY Warehouse کنترل کامل موجودی، ردیابی رویدادها و کاردکس لحظه‌ای را در یک پلتفرم تجربه کنید.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <RouterLink to="/register"
            class="bg-primary-400 hover:bg-primary-500 text-black font-bold text-lg px-8 py-4 rounded-xl transition-all w-full sm:w-auto"
            style="box-shadow:0 4px 24px rgba(128,161,255,0.4)">
            شروع رایگان ←
          </RouterLink>
          <RouterLink to="/login"
            class="border border-gray-700 hover:border-primary-400 text-gray-300 hover:text-white font-medium text-lg px-8 py-4 rounded-xl transition-all w-full sm:w-auto text-center">
            ورود به سیستم
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="py-16 px-6">
      <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6">
        <div v-for="s in stats" :key="s.label"
             class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center hover:border-primary-400/50 transition-all group">
          <div class="text-4xl font-black text-primary-400 mb-2 group-hover:scale-110 transition-transform">{{ s.value }}</div>
          <div class="text-gray-400 text-sm">{{ s.label }}</div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="py-16 px-6 bg-gray-950">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14">
          <h2 class="text-4xl font-bold text-white mb-3">ویژگی‌های اصلی</h2>
          <p class="text-gray-400 text-lg">همه آنچه برای مدیریت حرفه‌ای انبار نیاز دارید</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="f in features" :key="f.title"
               class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-primary-400/40 transition-all group">
            <div class="w-14 h-14 rounded-xl bg-primary-400/10 border border-primary-400/20 flex items-center justify-center mb-5 text-2xl group-hover:bg-primary-400/20 transition-all">
              {{ f.icon }}
            </div>
            <h3 class="text-xl font-bold text-white mb-3">{{ f.title }}</h3>
            <p class="text-gray-400 leading-relaxed text-sm">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-6">
      <div class="max-w-3xl mx-auto text-center bg-gray-900 border border-primary-400/20 rounded-3xl py-16 px-8">
        <img src="@/assets/logo.png" alt="SKY Warehouse" class="h-16 w-16 object-contain mx-auto mb-6" />
        <h2 class="text-4xl font-bold text-white mb-4">آماده شروع هستید؟</h2>
        <p class="text-gray-400 text-lg mb-8">همین حالا ثبت نام کنید و انبار خود را هوشمند کنید</p>
        <RouterLink to="/register"
          class="inline-block bg-primary-400 hover:bg-primary-500 text-black font-bold text-lg px-10 py-4 rounded-xl transition-all"
          style="box-shadow:0 4px 24px rgba(128,161,255,0.4)">
          شروع رایگان — SKY Warehouse
        </RouterLink>
      </div>
    </section>
  </div>
</template>

<script setup>
const stats = [
  { value: '۱۰۰۰+', label: 'کاربر فعال' },
  { value: '۵۰۰۰+', label: 'کالای ثبت شده' },
  { value: '۹۹.۹٪', label: 'آپتایم سیستم' },
  { value: '۲۴/۷', label: 'پشتیبانی' },
]
const features = [
  { icon: '📦', title: 'مدیریت کالا', desc: 'افزودن، ویرایش و حذف کالاها با اطلاعات کامل شامل کد، قیمت و موجودی.' },
  { icon: '📥', title: 'رویدادهای انبار', desc: 'ثبت ورود، خروج، مرجوعی، ضایعات و تعدیل موجودی با شماره سند.' },
  { icon: '📊', title: 'کاردکس کالا', desc: 'مشاهده تاریخچه کامل هر کالا با مانده تجمعی در هر بازه زمانی.' },
  { icon: '🔐', title: 'امنیت کامل', desc: 'احراز هویت امن با رمزنگاری پسوردها و توکن‌های دسترسی Sanctum.' },
  { icon: '📱', title: 'ریسپانسیو', desc: 'طراحی کاملاً سازگار با موبایل، تبلت و دسکتاپ.' },
  { icon: '⚡', title: 'سرعت بالا', desc: 'معماری SPA با Vue.js 3 و Laravel API برای تجربه کاربری روان.' },
]
</script>
