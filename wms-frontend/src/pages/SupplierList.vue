<template>
  <div class="supplier-list-page q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">🏭 Nhà cung cấp</div>
      <q-btn
        v-if="canManageSuppliers"
        color="primary"
        icon="add"
        label="Thêm nhà cung cấp"
        @click="$router.push('/suppliers/create')"
      />
    </div>

    <!-- Search -->
    <q-card class="q-mb-md">
      <q-card-section>
        <q-input
          v-model="searchQuery"
          placeholder="Tìm theo tên, SĐT, email..."
          outlined
          dense
          clearable
          @update:model-value="handleSearch"
        >
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </q-card-section>
    </q-card>

    <!-- Loading -->
    <div v-if="isLoading" class="text-center q-py-xl">
      <q-spinner color="primary" size="50px" />
      <div class="q-mt-md text-grey-6">Đang tải dữ liệu...</div>
    </div>

    <!-- Suppliers Table -->
    <q-card v-else-if="suppliers.length > 0">
      <q-table
        :rows="filteredSuppliers"
        :columns="columns"
        row-key="id"
        :rows-per-page-options="[10, 25, 50]"
        flat
      >
        <template #body-cell-actions="props">
          <q-td :props="props">
            <div class="row q-gutter-sm no-wrap">
              <q-btn
                flat
                dense
                round
                color="primary"
                icon="visibility"
                size="sm"
                @click="viewSupplier(props.row.id)"
              >
                <q-tooltip>Xem chi tiết</q-tooltip>
              </q-btn>
              <q-btn
                v-if="canManageSuppliers"
                flat
                dense
                round
                color="orange"
                icon="edit"
                size="sm"
                @click="editSupplier(props.row.id)"
              >
                <q-tooltip>Chỉnh sửa</q-tooltip>
              </q-btn>
              <q-btn
                v-if="canManageSuppliers"
                flat
                dense
                round
                color="negative"
                icon="delete"
                size="sm"
                @click="confirmDelete(props.row)"
              >
                <q-tooltip>Xóa</q-tooltip>
              </q-btn>
            </div>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Empty State -->
    <q-card v-else class="text-center q-py-xl">
      <q-icon name="local_shipping" size="80px" color="grey-4" />
      <div class="text-h6 text-grey-6 q-mt-md">Chưa có nhà cung cấp</div>
      <div class="text-grey-5 q-mb-md">Thêm nhà cung cấp đầu tiên để bắt đầu</div>
      <q-btn
        v-if="canManageSuppliers"
        color="primary"
        label="Thêm nhà cung cấp"
        @click="$router.push('/suppliers/create')"
      />
    </q-card>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Xác nhận xóa</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          Bạn có chắc chắn muốn xóa nhà cung cấp <strong>{{ supplierToDelete ? supplierToDelete.name : '' }}</strong>?
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Hủy" color="grey" v-close-popup />
          <q-btn
            flat
            label="Xóa"
            color="negative"
            :loading="isDeleting"
            @click="deleteSupplier"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from '@/stores/auth'
import { supplierApi } from '@/services/api'

const $q = useQuasar()
const router = useRouter()
const auth = useAuthStore()

const canManageSuppliers = computed(() => {
  const role = auth.user?.role || auth.user?.type || ''
  return role === 'admin'
})

const suppliers = ref([])
const isLoading = ref(false)
const searchQuery = ref('')
const deleteDialog = ref(false)
const supplierToDelete = ref(null)
const isDeleting = ref(false)

const columns = [
  { name: 'name', label: 'Tên nhà cung cấp', field: 'name', align: 'left', sortable: true },
  { name: 'contact_name', label: 'Người liên hệ', field: 'contact_name', align: 'left', sortable: true },
  { name: 'phone', label: 'SĐT', field: 'phone', align: 'left', sortable: true },
  { name: 'email', label: 'Email', field: 'email', align: 'left', sortable: true },
  { name: 'address', label: 'Địa chỉ', field: 'address', align: 'left' },
  {
    name: 'status',
    label: 'Trạng thái',
    field: 'status',
    align: 'left',
    sortable: true,
    format: (val) => {
      if (!val) return '—'
      return val === 'inactive' ? 'Ngưng hợp tác' : 'Đang hợp tác'
    }
  },
  { name: 'actions', label: 'Thao tác', field: 'actions', align: 'center' },
]

const viewSupplier = (id) => {
  router.push(`/suppliers/${id}`)
}

const filteredSuppliers = computed(() => {
  const q = (searchQuery.value || '').trim().toLowerCase()
  if (!q) return suppliers.value

  return suppliers.value.filter((s) => {
    const hay = [s.name, s.contact_name, s.phone, s.email, s.address]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
    return hay.includes(q)
  })
})

const loadSuppliers = async () => {
  isLoading.value = true
  try {
    const response = await supplierApi.getAll()
    suppliers.value = response.data || []
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Không thể tải danh sách nhà cung cấp'
    })
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  // search is computed client-side
}

const editSupplier = (id) => {
  router.push(`/suppliers/${id}/edit`)
}

const confirmDelete = (supplier) => {
  supplierToDelete.value = supplier
  deleteDialog.value = true
}

const deleteSupplier = async () => {
  if (!supplierToDelete.value) return

  isDeleting.value = true
  try {
    await supplierApi.delete(supplierToDelete.value.id)
    $q.notify({ type: 'positive', message: 'Xóa nhà cung cấp thành công' })
    deleteDialog.value = false
    supplierToDelete.value = null
    await loadSuppliers()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Không thể xóa nhà cung cấp'
    })
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  if (!canManageSuppliers.value) {
    $q.notify({ type: 'negative', message: 'Bạn không có quyền truy cập chức năng này' })
    router.push('/')
    return
  }

  loadSuppliers()
})
</script>
