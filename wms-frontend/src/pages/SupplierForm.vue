<template>
  <div class="supplier-form-page q-pa-md">
    <!-- Header -->
    <div class="row items-center q-mb-md">
      <q-btn flat dense round icon="arrow_back" @click="$router.back()" />
      <div class="text-h5 q-ml-md">
        {{ isEditMode ? '✏️ Chỉnh sửa nhà cung cấp' : '➕ Thêm nhà cung cấp' }}
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
      <div class="q-mt-md text-grey-6">Đang tải dữ liệu...</div>
    </div>

    <q-card v-else>
      <q-card-section>
        <q-form @submit="handleSubmit" class="q-gutter-md">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-input
                v-model="form.name"
                label="Tên nhà cung cấp *"
                outlined
                dense
                :rules="[val => !!val || 'Tên nhà cung cấp là bắt buộc']"
                :error="!!errors.name"
                :error-message="errors.name"
                @update:model-value="errors.name = ''"
              >
                <template #prepend>
                  <q-icon name="business" />
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.contact_name"
                label="Người liên hệ"
                outlined
                dense
                :error="!!errors.contact_name"
                :error-message="errors.contact_name"
                @update:model-value="errors.contact_name = ''"
              >
                <template #prepend>
                  <q-icon name="person" />
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.phone"
                label="Số điện thoại"
                outlined
                dense
                :error="!!errors.phone"
                :error-message="errors.phone"
                @update:model-value="errors.phone = ''"
              >
                <template #prepend>
                  <q-icon name="phone" />
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.email"
                label="Email"
                outlined
                dense
                :error="!!errors.email"
                :error-message="errors.email"
                @update:model-value="errors.email = ''"
              >
                <template #prepend>
                  <q-icon name="mail" />
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-input
                v-model="form.address"
                label="Địa chỉ"
                outlined
                dense
                :error="!!errors.address"
                :error-message="errors.address"
                @update:model-value="errors.address = ''"
              >
                <template #prepend>
                  <q-icon name="location_on" />
                </template>
              </q-input>
            </div>

            <div class="col-12 col-md-6">
              <q-select
                v-model="form.status"
                :options="statusOptions"
                emit-value
                map-options
                label="Trạng thái"
                outlined
                dense
                :error="!!errors.status"
                :error-message="errors.status"
                @update:model-value="errors.status = ''"
              >
                <template #prepend>
                  <q-icon name="toggle_on" />
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-gutter-sm justify-end q-mt-md">
            <q-btn flat label="Hủy" color="grey" @click="$router.back()" />
            <q-btn
              type="submit"
              :label="isEditMode ? 'Cập nhật' : 'Tạo mới'"
              color="primary"
              :loading="isSaving"
              :disable="isSaving"
            />
          </div>
        </q-form>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from '@/stores/auth'
import { supplierApi } from '@/services/api'

const $q = useQuasar()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const canManageSuppliers = computed(() => {
  const role = auth.user?.role || auth.user?.type || ''
  return role === 'admin'
})

const isEditMode = computed(() => !!route.params.id)
const supplierId = computed(() => route.params.id)

const isLoading = ref(false)
const isSaving = ref(false)
const errors = ref({})

const form = ref({
  name: '',
  contact_name: '',
  phone: '',
  email: '',
  address: '',
  status: 'active'
})

const statusOptions = [
  { label: 'Đang hợp tác', value: 'active' },
  { label: 'Ngưng hợp tác', value: 'inactive' }
]

const loadSupplier = async () => {
  if (!isEditMode.value) return

  isLoading.value = true
  try {
    const response = await supplierApi.getById(supplierId.value)
    const supplier = response.data

    form.value = {
      name: supplier.name || '',
      contact_name: supplier.contact_name || '',
      phone: supplier.phone || '',
      email: supplier.email || '',
      address: supplier.address || '',
      status: supplier.status || 'active'
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Không thể tải thông tin nhà cung cấp'
    })
    router.push('/suppliers')
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  errors.value = {}
  isSaving.value = true

  try {
    if (isEditMode.value) {
      await supplierApi.update(supplierId.value, form.value)
      $q.notify({ type: 'positive', message: 'Cập nhật nhà cung cấp thành công' })
    } else {
      await supplierApi.create(form.value)
      $q.notify({ type: 'positive', message: 'Tạo nhà cung cấp thành công' })
    }

    router.push('/suppliers')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }

    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Có lỗi xảy ra'
    })
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  if (!canManageSuppliers.value) {
    $q.notify({ type: 'negative', message: 'Bạn không có quyền truy cập chức năng này' })
    router.push('/')
    return
  }

  loadSupplier()
})
</script>
