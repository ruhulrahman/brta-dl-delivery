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
      <b-breadcrumb-item active>Subscriber List</b-breadcrumb-item>
    </b-breadcrumb>
      <div class="form-wrapper">
      <b-card title="Search">
          <b-card-text>
              <b-row style="font-size: 13px;">
                <b-col lg="3" md="3" sm="3" xs="6">
                  <b-form-group
                    id="name"
                    label="Name"
                    label-for="name"
                  >
                  <template v-slot:label>
                    Name
                  </template>
                    <b-form-input
                      id="name"
                      v-model="search.name"
                      placeholder="Enter Name"
                    ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col lg="3" md="3" sm="3" xs="6">
                  <b-form-group
                    id="phone"
                    label-for="phone"
                  >
                  <template v-slot:label>
                    Phone
                  </template>
                    <b-form-input
                      id="phone"
                      v-model="search.phone"
                      placeholder="Enter Phone"
                    ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col lg="3" md="3" sm="3" xs="6">
                  <b-form-group
                    id="email"
                    label-for="email"
                  >
                  <template v-slot:label>
                    Email
                  </template>
                    <b-form-input
                      id="email"
                      v-model="search.email"
                      placeholder="Enter Email"
                    ></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col lg="3" md="3" sm="3" xs="6">
                  <b-form-group
                  label-for="subcription_plan_id"
                  >
                  <template v-slot:label>
                  Subscription Plan
                  </template>
                  <b-form-select
                      plain
                      v-model="search.subcription_plan_id"
                      :options="subscriptionPlanList"
                      id="subcription_plan_id"
                    >
                    <template v-slot:first>
                    <b-form-select-option :value=0>Select</b-form-select-option>
                  </template>
                  </b-form-select>
                </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <b-col sm="12" md="12" class="text-right">
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
          <h4 class="card-title mb-0 pl-0">Subscriber List</h4>
        </b-col>
      </b-row>
    </b-card-title>
      <b-row>
      <b-col>
        <b-overlay :show="loading">
            <div class="table-wrapper table-responsive">
              <table class="table table-striped table-hover table-bordered">
                <thead>
                  <tr style="font-size: 12px;">
                    <th scope="col" class="text-center">SL</th>
                    <th scope="col" class="text-center">User Info</th>
                    <!-- <th scope="col" class="text-center">Active Device</th> -->
                    <th scope="col" class="text-center">Subscription Plan</th>
                    <th scope="col" class="text-center">Subscription Start Date</th>
                    <th scope="col" class="text-center">Expire Date</th>
                    <th scope="col" class="text-center">Action</th>
                  </tr>
                </thead>
                <template v-if="listData.length > 0">
                  <tbody v-for="(item, index) in listData" :key="index">
                    <tr style="font-size: 11px;">
                      <td scope="row" class="text-center">{{ index + pagination.slOffset }}</td>
                      <td class="text-left">
                        <span v-if="item.user">
                          <span v-html="item.user.name"></span><br/>
                          <span v-html="item.user.email"></span><br/>
                          <span v-html="item.user.phone"></span>
                        </span>
                      </td>
                      <!-- <td class="text-center">IOS</td> -->
                      <td class="text-center">{{ item.subcription_plan ? item.subcription_plan.plan_title : '' }}</td>
                      <td class="text-center">{{ item.start_at ? dDate(item.start_at) : '' }}</td>
                      <td class="text-center">{{ item.expire_at ? dDate(item.expire_at) : '' }}</td>
                      <td class="text-center">
                        <a v-tooltip="'Delete'" v-if="has_permission('delete_subscriber')" @click="deleteData(item)" style="width: 20px !important; height: 20px !important; font-size:10px" href="javascript:" class="action-btn delete"><i class="ri-delete-bin-2-line"></i></a>
                      </td>
                    </tr>
                  </tbody>
                 </template>
                 <template v-else>
                    <tr>
                      <td colspan="12" class="notFound">Data Not Found</td>
                    </tr>
                 </template>
              </table>
            </div>
        </b-overlay>
      </b-col>
    </b-row>
  </b-card>
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
import RestApi, { baseURL } from '@/config'
export default {
  data () {
    return {
      // pagination
      rows: 100,
      currentPage: 1,
      // form data
      search: {
        name: '',
        email: '',
        phone: '',
        subcription_plan_id: 0
      },
      value: '',
      listData: [],
      loading: false,
      editItem: '',
      subscriptionPlanList: [],
      paymentMediaList: [],
      paymentStatuses: []
    }
  },
  created () {
    this.getListData()
  },
  mounted () {
    // this.getSubscriptionPlanList()
    // this.makeEncryptAllBookContent()
  },
  computed: {
  },
  methods: {
    // async makeEncryptAllBookContent () {
    //   await RestApi.getData(baseURL, 'api/v1/admin/ajax/make_encrypt_all_book_contents')
    // },
    searchData () {
      this.getListData()
    },
    clearData () {
      this.search = {
        name: '',
        email: '',
        phone: '',
        subcription_plan_id: 0
      }
      this.getListData()
    },
    async getListData () {
      this.loading = true
      const params = Object.assign({}, this.search, { page: this.pagination.currentPage, per_page: this.pagination.perPage })
      var result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_subscription_user_list', params)
      if (result.success) {
        this.listData = result.data.data
        this.paginationData(result.data)
      } else {
        this.listData = []
      }
      this.loading = false
    },
    deleteData (item) {
      this.$swal({
        title: 'Are you sure to delete?',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        focusConfirm: false
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteConfirmation(item)
        }
      })
    },
    async deleteConfirmation (item) {
      this.loading = true
      var result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/delete_subscriber_data', item)
      if (result.success) {
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
        this.getListData()
      }
      this.loading = false
    },
    getSubscriptionPlanList () {
      RestApi.getData(baseURL, 'api/v1/admin/ajax/get_subscription_plan_all_list', null).then(response => {
        if (response.success) {
          var data = response.data
          this.subscriptionPlanList = data.filter(obj => obj.value !== null && obj.text !== null) // Exclude items with null values
            .map((obj, index) => {
              return { value: obj.value, text: obj.text }
            })
        } else {
          this.subscriptionPlanList = []
        }
      })
    }
  }
}
</script>
<style>
 .notFound {
   text-align: center;
 }
</style>
