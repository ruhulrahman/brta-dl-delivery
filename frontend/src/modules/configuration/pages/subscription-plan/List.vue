<template>
  <div class="section-wrapper">
    <!-- <div class="breadcrumb-wrapper">
      <b-breadcrumb class="custom-bread"></b-breadcrumb>
    </div> -->
    <b-breadcrumb>
      <b-breadcrumb-item to="/dashboard">
        <b-icon icon="house-fill" scale="1.25" shift-v="1.25" aria-hidden="true"></b-icon>
        Dashboard
      </b-breadcrumb-item>
      <!-- <b-breadcrumb-item to="/user">Manage Book</b-breadcrumb-item> -->
      <b-breadcrumb-item active>Subscription Plan</b-breadcrumb-item>
    </b-breadcrumb>
      <div class="form-wrapper">
      <b-card title="Subscription Plan Search">
          <b-card-text>
              <b-row style="font-size: 13px;">
                <b-col sm="12" md="3">
                  <b-form-group
                      id="plan_title"
                      label="Plan Title"
                      label-for="Plan Title"
                  >
                      <b-form-input
                      id="plan_title"
                      v-model="search.plan_title"
                      type="text"
                      placeholder="Enter Plan Title"
                      required
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="plan_duration"
                      label="Plan Duration"
                      label-for="Plan Duration"
                  >
                      <b-form-input
                      id="plan_duration"
                      v-model="search.plan_duration"
                      type="text"
                      placeholder="Enter Plan Duration "
                      required
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <b-form-group
                      id="plan_price"
                      label="Plan Price"
                      label-for="Plan Price"
                  >
                      <b-form-input
                      id="plan_price"
                      v-model="search.plan_price"
                      placeholder="Enter Plan Price"
                      required
                      ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col sm="12" md="3">
                  <br>
                  <b-button size="sm" variant="primary" @click="searchData"><i class="ri-search-line"></i> Search</b-button>
                  <b-button size="sm ml-1" variant="danger" @click="clearData"><i class="ri-close-line"></i> Clear</b-button>
                </b-col>
              </b-row>
          </b-card-text>
      </b-card>
  </div>
  <b-card class="mt-3">
    <b-card-title>
      <b-row>
        <b-col>
          <h4 class="card-title mb-0 pl-0">Subscription Plan List</h4>
        </b-col>
        <b-col class="text-right">
          <b-button v-if="has_permission('add_new_subscription_plan')" size="sm" variant="info" @click="openAddNewModal()">Add New<i class="ri-add-fill"></i></b-button>
        </b-col>
      </b-row>
    </b-card-title>
    <b-row>
      <b-col>
        <b-overlay :show="loading">
          <b-card>
            <div class="table-wrapper table-responsive">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr style="font-size: 13px;">
                    <th scope="col" class="text-center">SL</th>
                    <th scope="col" class="text-center">Plan Title</th>
                    <th scope="col" class="text-center">Plan Duration</th>
                    <th scope="col" class="text-center">Plan Benefit</th>
                    <th scope="col" class="text-center">Plan Price</th>
                    <th scope="col" class="text-center">Active</th>
                    <th scope="col" class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody v-for="(item, index) in listData" :key="index">
                  <tr style="font-size: 12px;">
                    <td scope="row" class="text-center">{{ index + pagination.slOffset }}</td>
                    <td class="text-center">{{ item.plan_title }}</td>
                    <td class="text-center">{{ item.plan_duration }} <span>Month</span></td>
                    <td class="text-center"><span v-html="item.plan_benefits"></span></td>
                    <td class="text-center">{{ item.plan_price }}</td>
                    <td class="text-center">
                      <b-form-checkbox v-if="has_permission('active_or_deactive_subscription_plan')" @change="toggleActiveStatus(item)" v-model="item.active" name="check-button" switch>
                      </b-form-checkbox>
                    </td>
                    <td class="text-center">
                      <a v-tooltip="'Edit'" v-if="has_permission('edit_subscription_plan')" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn edit" @click="editData(item)"><i class="ri-pencil-fill"></i></a>
                      <a v-tooltip="'Delete'" v-if="has_permission('delete_subscription_plan')" @click="deleteConfirmation(item)" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn delete"><i class="ri-delete-bin-2-line"></i></a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </b-card>
        </b-overlay>
      </b-col>
    </b-row>
  </b-card>
    <b-modal id="modal-1" ref="editModal" size="lg" title="Subscription Plan" centered :hide-footer="true">
      <Form @loadList="loadData" :editItem="editItem"/>
    </b-modal>
    <!-- pagination -->
    <div class="pagination-wrapper mt-4">
      <span>Showing {{ pagination.slOffset }} from {{ pagination.totalRows }} entries</span>
      <b-pagination
        size="sm"
        v-model="pagination.currentPage"
        :per-page="pagination.perPage"
        :total-rows="pagination.totalRows"
        @input="searchData"
        />
    </div>
  </div>
</template>

<script>
import Form from './Form.vue'
import RestApi, { baseURL } from '@/config'
// import { permissionList } from '../../../api/routes'
export default {
  components: {
    Form
  },

  data () {
    return {
      // pagination
      rows: 100,
      currentPage: 1,
      // form data
      search: {
        plan_title: '',
        plan_duration: '',
        plan_price: '',
        plan_benefits: ''
      },
      value: '',
      listData: [],
      loading: false,
      editItem: ''
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    openAddNewModal () {
      this.editItem = ''
      this.$refs.editModal.show()
    },
    editData (item) {
      this.editItem = item
      this.$refs.editModal.show()
    },
    searchData () {
      this.loadData()
    },
    clearData () {
      this.search = {
        plan_title: '',
        plan_duration: '',
        plan_price: '',
        plan_benefits: ''
      }
      this.loadData()
    },
    async loadData () {
      this.loading = true
      const params = Object.assign({}, this.search, { page: this.pagination.currentPage, per_page: this.pagination.perPage })
      var result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_subscription_plan_list', params)
      if (result.success) {
        this.listData = result.data.data
        this.paginationData(result.data)
      }
      this.loading = false
    },
    async toggleActiveStatus (item) {
      this.loading = true
      var result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/toggle_subcription_plan_active_status', item)
      if (result.success) {
        this.$toast.success({ title: 'Success', message: result.message })
        this.loadData()
      }
      this.loading = false
    },
    deleteConfirmation (item) {
      this.$swal({
        title: 'Are you sure to delete?',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          // declare confirmed method to hit api
          this.deleteRole(item)
        }
      })
    },
    async deleteRole (item) {
      this.loading = true
      var result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/delete_subscription_plan_data', item)
      if (result.success) {
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
        this.loadData()
      }
      this.loading = false
    }
  }
}
</script>
