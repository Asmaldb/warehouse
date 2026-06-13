<template>
  <div class="min-h-screen bg-black pt-16">
    <div class="max-w-5xl mx-auto px-4 py-20">
      <div class="text-center mb-16">
        <h1 class="text-5xl font-black text-white mb-6">درباره ما</h1>
        <p class="text-gray-400 text-xl max-w-2xl mx-auto leading-relaxed">
          ما یک تیم متخصص در توسعه نرم‌افزارهای مدیریت انبار هستیم با بیش از ۵ سال تجربه
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-12 mb-20">
        <div class="animate-slide-up">
          <h2 class="text-2xl font-bold text-white mb-4">ماموریت ما</h2>
          <p class="text-gray-400 leading-relaxed mb-4">
            هدف ما ارائه ابزارهای هوشمند و کارآمد برای مدیریت انبار و موجودی کالاهاست.
            ما باور داریم که هر کسب‌وکار، صرف‌نظر از اندازه‌اش، باید به ابزارهای حرفه‌ای
            دسترسی داشته باشد.
          </p>
          <p class="text-gray-400 leading-relaxed">
            سیستم ما با استفاده از جدیدترین تکنولوژی‌ها طراحی شده تا تجربه‌ای روان،
            سریع و قابل اطمینان برای کاربران فراهم کند.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div v-for="val in values" :key="val.title" class="card">
            <div class="text-primary-400 mb-2">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="val.icon"/>
              </svg>
            </div>
            <h3 class="text-white font-semibold mb-1">{{ val.title }}</h3>
            <p class="text-gray-500 text-xs">{{ val.desc }}</p>
          </div>
        </div>
      </div>

      <div class="glass p-12 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">با ما همراه شوید</h2>
        <p class="text-gray-400 mb-8">سیستم مدیریت انبار خود را همین امروز آغاز کنید</p>
        <RouterLink to="/register" class="btn-primary text-lg px-8 py-3">شروع رایگان</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
const values = [
  { title: 'سادگی',       desc: 'رابط کاربری ساده و قابل فهم',   icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
  { title: 'امنیت',       desc: 'حفاظت از داده‌های شما',          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { title: 'سرعت',        desc: 'عملکرد سریع و بهینه',             icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
  { title: 'پشتیبانی',   desc: 'تیم پشتیبانی همیشه در دسترس',   icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z' },
]
</script>