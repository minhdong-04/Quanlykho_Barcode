<template>
  <div class="user-form-page q-pa-md">
    <!-- Header -->
    <div class="row items-center q-mb-md">
      <q-btn
        flat
        dense
        round
        icon="arrow_back"
        @click="$router.back()"
      />
      <div class="text-h5 q-ml-md">
        {{ isEditMode ? '✏️ Chỉnh sửa người dùng' : '➕ Thêm người dùng' }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
      <div class="q-mt-md text-grey-6">Đang tải dữ liệu...</div>
    </div>

    <!-- Form Card -->
    <q-card v-else>
      <q-card-section class="q-pa-lg">
        <div class="row q-col-gutter-lg">
          <!-- Basic Info -->
          <div class="col-12 col-md-6">
            <q-input
              v-model="form.name"
              label="Tên người dùng *"
              outlined
              dense
              :error="!!errors.name"
              :error-message="errors.name"
              @blur="validateField('name')"
            />
          </div>

          <div class="col-12 col-md-6">
            <q-input
              v-model="form.email"
              label="Email *"
              type="email"
              outlined
              dense
              :error="!!errors.email"
              :error-message="errors.email"
              @blur="validateField('email')"
            />
          </div>

          <!-- Password -->
          <div class="col-12 col-md-6">
            <q-input
              v-model="form.password"
              :label="isEditMode ? 'Mật khẩu (để trống nếu không thay đổi)' : 'Mật khẩu *'"
              type="password"
              outlined
              dense
              :error="!!errors.password"
              :error-message="errors.password"
              @blur="validateField('password')"
            />
          </div>

          <!-- Confirm Password -->
          <div class="col-12 col-md-6">
            <q-input
              v-model="form.password_confirmation"
              label="Xác nhận mật khẩu"
              type="password"
              outlined
              dense
              :error="!!errors.password_confirmation"
              :error-message="errors.password_confirmation"
              @blur="validateField('password_confirmation')"
            />
          </div>

          <!-- Role Selection -->
          <div class="col-12">
            <q-select
              v-model="form.role"
              :options="roleOptions"
              option-value="value"
              option-label="label"
              label="Vai trò *"
              outlined
              dense
              emit-value
              map-options
              :error="!!errors.role"
              :error-message="errors.role"
              @blur="validateField('role')"
            />
            <div class="text-caption text-grey q-mt-sm">
              <div><strong>admin:</strong> Quản trị viên toàn hệ thống</div>
              <div><strong>manager:</strong> Quản lý kho (xem báo cáo, nhập/xuất)</div>
              <div><strong>warehouse_staff:</strong> Nhân viên kho (nhập/xuất)</div>
            </div>
          </div>
        </div>
      </q-card-section>

      <!-- Actions -->
      <q-card-section class="q-pa-lg bg-grey-1">
        <div class="row q-col-gutter-md justify-end">
          <div class="col-auto">
            <q-btn
              label="Hủy"
              flat
              color="grey"
              @click="$router.back()"
            />
          </div>
          <div class="col-auto">
            <q-btn
              :label="isEditMode ? 'Cập nhật' : 'Tạo'"
              color="primary"
              :loading="isSubmitting"
              @click="submitForm"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { userApi } from '@/services/api'

const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// State
const isLoading = ref(false)
const isSubmitting = ref(false)
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'warehouse_staff'
})
const errors = ref({})

const roleOptions = [
  { label: 'Quản trị viên', value: 'admin' },
  { label: 'Quản lý', value: 'manager' },
  { label: 'Nhân viên kho', value: 'warehouse_staff' }
]

const isEditMode = computed(() => {
  return !!route.params.id
})

// Methods
const loadUser = async () => {
  if (!isEditMode.value) return
  
  isLoading.value = true
  try {
    const response = await userApi.getById(route.params.id)
    const user = response.data
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      password_confirmation: '',
      role: user.role
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Không thể tải dữ liệu người dùng'
    })
    router.push('/users')
  } finally {
    isLoading.value = false
  }
}

const validateField = (field) => {
  errors.value[field] = ''
  
  if (field === 'name') {
    if (!form.value.name.trim()) {
      errors.value.name = 'Vui lòng nhập tên'
    }
  }
  
  if (field === 'email') {
    if (!form.value.email.trim()) {
      errors.value.email = 'Vui lòng nhập email'
    } else if (!isValidEmail(form.value.email)) {
      errors.value.email = 'Email không hợp lệ'
    }
  }
  
  if (field === 'password') {
    if (!isEditMode.value && !form.value.password) {
      errors.value.password = 'Vui lòng nhập mật khẩu'
    } else if (form.value.password && form.value.password.length < 6) {
      errors.value.password = 'Mật khẩu phải có ít nhất 6 ký tự'
    }
  }
  
  if (field === 'password_confirmation') {
    if (form.value.password && form.value.password !== form.value.password_confirmation) {
      errors.value.password_confirmation = 'Mật khẩu xác nhận không khớp'
    }
  }
  
  if (field === 'role') {
    if (!form.value.role) {
      errors.value.role = 'Vui lòng chọn vai trò'
    }
  }
}

const validateAllFields = () => {
  errors.value = {}
  
  if (!form.value.name.trim()) {
    errors.value.name = 'Vui lòng nhập tên'
  }
  
  if (!form.value.email.trim()) {
    errors.value.email = 'Vui lòng nhập email'
  } else if (!isValidEmail(form.value.email)) {
    errors.value.email = 'Email không hợp lệ'
  }
  
  if (!isEditMode.value && !form.value.password) {
    errors.value.password = 'Vui lòng nhập mật khẩu'
  } else if (form.value.password && form.value.password.length < 6) {
    errors.value.password = 'Mật khẩu phải có ít nhất 6 ký tự'
  }
  
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Mật khẩu xác nhận không khớp'
  }
  
  if (!form.value.role) {
    errors.value.role = 'Vui lòng chọn vai trò'
  }
  
  return Object.keys(errors.value).length === 0
}

const isValidEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

const submitForm = async () => {
  if (!validateAllFields()) {
    $q.notify({
      type: 'warning',
      message: 'Vui lòng sửa các lỗi trước khi gửi'
    })
    return
  }
  
  isSubmitting.value = true
  try {
    const data = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role
    }
    
    // Only include password if it's set
    if (form.value.password) {
      data.password = form.value.password
      data.password_confirmation = form.value.password_confirmation
    }
    
    if (isEditMode.value) {
      await userApi.update(route.params.id, data)
      $q.notify({
        type: 'positive',
        message: 'Cập nhật người dùng thành công'
      })
    } else {
      await userApi.create(data)
      $q.notify({
        type: 'positive',
        message: 'Tạo người dùng thành công'
      })
    }
    
    router.push('/users')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Có lỗi xảy ra'
    })
  } finally {
    isSubmitting.value = false
  }
}

// Lifecycle
onMounted(() => {
  if (isEditMode.value) {
    loadUser()
  }
})
</script>

<style scoped>
.user-form-page {
  max-width: 600px;
  margin: 0 auto;
}
</style>
