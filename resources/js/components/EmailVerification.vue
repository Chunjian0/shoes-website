<template>
  <div class="email-verification">
    <div class="verification-step" v-if="step === 1">
      <h3 class="text-lg font-medium text-gray-900">{{ title || '邮箱验证' }}</h3>
      <p class="mt-1 text-sm text-gray-600" v-if="description">{{ description }}</p>
      
      <div class="mt-4">
        <label for="email" class="block text-sm font-medium text-gray-700">邮箱地址</label>
        <input
          type="email"
          id="email"
          v-model="email"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          :placeholder="emailPlaceholder || '请输入邮箱地址'"
          :disabled="loading"
        />
        <p v-if="emailError" class="mt-2 text-sm text-red-600">{{ emailError }}</p>
      </div>
      
      <div class="mt-4 flex justify-end">
        <button
          type="button"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :disabled="loading || !email"
          @click="sendVerificationCode"
        >
          <span v-if="loading">发送中...</span>
          <span v-else>{{ sendButtonText || '发送验证码' }}</span>
        </button>
      </div>
    </div>
    
    <div class="verification-step" v-if="step === 2">
      <h3 class="text-lg font-medium text-gray-900">{{ verificationTitle || '输入验证码' }}</h3>
      <p class="mt-1 text-sm text-gray-600">
        验证码已发送至 <span class="font-medium">{{ email }}</span>
        <button
          type="button"
          class="ml-2 text-sm text-blue-600 hover:text-blue-800"
          @click="step = 1"
        >
          更改
        </button>
      </p>
      
      <div class="mt-4">
        <label for="code" class="block text-sm font-medium text-gray-700">验证码</label>
        <input
          type="text"
          id="code"
          v-model="code"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          placeholder="请输入验证码"
          :disabled="verifying"
        />
        <p v-if="codeError" class="mt-2 text-sm text-red-600">{{ codeError }}</p>
      </div>
      
      <div class="mt-4 flex justify-between">
        <button
          type="button"
          class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          :disabled="loading"
          @click="resendCode"
        >
          <span v-if="resending">重新发送中...</span>
          <span v-else-if="countdown > 0">{{ countdown }}秒后可重发</span>
          <span v-else>重发验证码</span>
        </button>
        
        <button
          type="button"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          :disabled="verifying || !code"
          @click="verifyCode"
        >
          <span v-if="verifying">验证中...</span>
          <span v-else>{{ verifyButtonText || '验证' }}</span>
        </button>
      </div>
    </div>
    
    <div class="verification-step" v-if="step === 3">
      <div class="text-center py-4">
        <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <h3 class="mt-2 text-lg font-medium text-gray-900">{{ successTitle || '验证成功' }}</h3>
        <p class="mt-1 text-sm text-gray-600">{{ successMessage || '您的邮箱已成功验证。' }}</p>
        
        <div class="mt-4">
          <button
            v-if="showContinueButton"
            type="button"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="$emit('continue')"
          >
            {{ continueButtonText || '继续' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EmailVerification',
  props: {
    // 自定义文本
    title: String,
    description: String,
    emailPlaceholder: String,
    sendButtonText: String,
    verificationTitle: String,
    verifyButtonText: String,
    successTitle: String,
    successMessage: String,
    continueButtonText: String,
    
    // API配置
    sendUrl: {
      type: String,
      default: '/api/customer/send-verification-code'
    },
    verifyUrl: {
      type: String,
      default: '/api/customer/verify-email'
    },
    
    // 行为控制
    initialEmail: String,
    showContinueButton: {
      type: Boolean,
      default: true
    },
    countdownTime: {
      type: Number,
      default: 60
    }
  },
  data() {
    return {
      step: 1,
      email: this.initialEmail || '',
      code: '',
      emailError: '',
      codeError: '',
      loading: false,
      verifying: false,
      resending: false,
      countdown: 0,
      countdownTimer: null
    }
  },
  methods: {
    async sendVerificationCode() {
      if (!this.email) {
        this.emailError = '请输入邮箱地址';
        return;
      }
      
      this.loading = true;
      this.emailError = '';
      
      try {
        const response = await fetch(this.sendUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify({ email: this.email })
        });
        
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
          this.step = 2;
          this.startCountdown();
          this.$emit('code-sent', { email: this.email });
        } else {
          this.emailError = result.message || '验证码发送失败，请稍后再试';
        }
      } catch (error) {
        console.error('Error sending verification code:', error);
        this.emailError = '网络错误，请稍后再试';
      } finally {
        this.loading = false;
      }
    },
    
    async verifyCode() {
      if (!this.code) {
        this.codeError = '请输入验证码';
        return;
      }
      
      this.verifying = true;
      this.codeError = '';
      
      try {
        const response = await fetch(this.verifyUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify({
            email: this.email,
            verification_code: this.code
          })
        });
        
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
          this.step = 3;
          this.$emit('verified', { email: this.email });
        } else {
          this.codeError = result.message || '验证失败，请检查验证码是否正确';
        }
      } catch (error) {
        console.error('Error verifying code:', error);
        this.codeError = '网络错误，请稍后再试';
      } finally {
        this.verifying = false;
      }
    },
    
    async resendCode() {
      if (this.countdown > 0 || this.resending) {
        return;
      }
      
      this.resending = true;
      this.codeError = '';
      
      try {
        const response = await fetch(this.sendUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify({ email: this.email })
        });
        
        const result = await response.json();
        
        if (response.ok && result.status === 'success') {
          this.startCountdown();
          this.$emit('code-resent', { email: this.email });
        } else {
          this.codeError = result.message || '验证码重发失败，请稍后再试';
        }
      } catch (error) {
        console.error('Error resending verification code:', error);
        this.codeError = '网络错误，请稍后再试';
      } finally {
        this.resending = false;
      }
    },
    
    startCountdown() {
      this.countdown = this.countdownTime;
      clearInterval(this.countdownTimer);
      
      this.countdownTimer = setInterval(() => {
        if (this.countdown > 0) {
          this.countdown--;
        } else {
          clearInterval(this.countdownTimer);
        }
      }, 1000);
    }
  },
  beforeUnmount() {
    clearInterval(this.countdownTimer);
  }
}
</script>

<style scoped>
.email-verification {
  width: 100%;
}

.verification-step {
  width: 100%;
}
</style> 