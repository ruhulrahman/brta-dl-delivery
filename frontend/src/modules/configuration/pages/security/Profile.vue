<template>
  <b-overlay :show="loading">
  <div class="formBoder">
    <ValidationObserver ref="form" v-slot="{ handleSubmit, reset }">
      <b-form @submit.prevent="handleSubmit(updateData)" @reset.prevent="reset" autocomplete="off">
      <b-row>
        <b-col lg="3" md="3" sm="3" xs="12" offset="1">
          <ValidationProvider name="Photo" vid="Photo">
            <b-form-group
              label-for="photo"
              slot-scope="{ valid, errors }"
            >
            <template v-slot:label>
              <img v-if="currentUploadedPhoto" :src="currentUploadedPhoto" class="mr-2" width="70" alt="Profile Image">
              <img v-else src="../../../../../assets//images/man.png" class="mr-2" width="70" alt="Profile Image">
            </template>

            <!-- <input type="file" v-on:change="uploadFile" ref="file"/> -->

            <b-form-file
            class="mt-3"
              id="photo"
              v-on:change="onFileChange"
              accept="image/*"
              v-model="form.photo"
              ref="file"
              :state="errors[0] ? false : (valid ? true : null)"
              ></b-form-file>
            <div class="invalid-feedback">
              {{ errors[0] }}
            </div>
            <!-- <b-img style="margin-bottom: 10px" width='100px' v-if="form.id && form.photo" :src="baseURL + form.photo" fluid alt="Profile Photo"></b-img> -->
            </b-form-group>
          </ValidationProvider>
        </b-col>
        <b-col lg="6" md="6" sm="6" xs="12">
          <div class="profile">
            <h5>Name:
              <ValidationProvider name="Name" vid="name" rules="required" v-slot="{ errors }">
                <b-form-input
                  id="name"
                  v-model="form.name"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
            </ValidationProvider>
            </h5>
            <!-- <h5>Email:
              <ValidationProvider name="Email" vid="email" rules="required" v-slot="{ errors }">
              <b-form-input
              id="email"
                type="email"
                v-model="form.email"
                :state="errors[0] ? false : (valid ? true : null)"
              ></b-form-input>
              <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
            </ValidationProvider>
            </h5> -->
            <h5>Phone:
              <ValidationProvider name="Phone" vid="phone" rules="required" v-slot="{ errors }">
              <b-form-input
               id="phone"
                v-model="form.phone"
                :state="errors[0] ? false : (valid ? true : null)"
              ></b-form-input>
              <div class="invalid-feedback">
                {{ errors[0] }}
              </div>
            </ValidationProvider>
            </h5>
          </div>
        </b-col>
      </b-row>
      <div class="row mt-3">
        <div class="col-sm-3"></div>
        <div class="col text-right">
          <b-button type="submit" variant="primary" class="mr-2">Update</b-button>
          &nbsp;
          <b-button variant="danger" class="mr-1" @click="$bvModal.hide('modal-1')">Cancel</b-button>
        </div>
      </div>
     </b-form>
    </ValidationObserver>
    </div>
  </b-overlay>
</template>
<script>
import RestApi, { baseURL } from '@/config'

export default {
  props: ['editItem'],
  data () {
    return {
      form: {
        name: '',
        email: '',
        phone: '',
        photo: null
      },
      currentUploadedPhoto: '',
      upload_photo: [],
      errors: [],
      valid: null,
      loading: false,
      fileValidationMsz: '',
      value: null
    }
  },
  created () {
    this.getProfileList()
  },
  mounted () {
  },
  methods: {
    // handlePhoto (e) {
    //   this.fileValidationMsz = ''
    //   const input = event.target
    //   const file = input.files[0]
    //   if (file.size > 1024 * 1024) {
    //     e.preventDefault()
    //     this.fileValidationMsz = 'Maximum file size must be 1MB'
    //   }
    //   if (input.files && input.files[0]) {
    //     const reader = new FileReader()
    //     reader.onload = (e) => {
    //       this.form.photo = e.target.result
    //     }
    //     reader.readAsDataURL(input.files[0])
    //   } else {
    //     this.form.photo = ''
    //   }
    // },
    onFileChange (event) {
      this.upload_photo = event.target.files[0]

      var reader = new FileReader()
      reader.onload = function () {
        var dataURL = reader.result
        this.currentUploadedPhoto = dataURL
      }
      reader.readAsDataURL(this.upload_photo)
    },
    // async uploadFile () {
    //   this.upload_photo = this.$refs.file.files[0]
    //   console.log('this.upload_photo', this.upload_photo)
    //   var formData = new FormData()
    //   if (this.upload_photo) {
    //     formData.append('photo', this.upload_photo)
    //   }
    //   console.log('this.upload_photo', this.upload_photo)
    //   const result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/upload_user_photo', formData)
    //   this.loading = false
    //   if (result.success) {
    //     console.log('result', result)
    //     this.$store.dispatch('Auth/setAuthUser', result.authUser)
    //     this.$toast.success({
    //       title: 'Success',
    //       message: result.message
    //     })
    //     this.$bvModal.hide('modal-1')
    //   } else {
    //     this.$refs.form.setErrors(result.errors)
    //   }
    // },
    async updateData () {
      this.loading = true
      let result = ''

      var formData = new FormData()
      Object.keys(this.form).map(key => {
        formData.append(key, this.form[key])
      })
      if (this.upload_photo) {
        formData.append('photo', this.upload_photo)
      }
      console.log('this.upload_photo', this.upload_photo)
      result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/profile_update_data', formData)
      this.loading = false
      if (result.success) {
        console.log('result', result)
        this.$store.dispatch('Auth/setAuthUser', result.authUser)
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
        this.$bvModal.hide('modal-1')
      } else {
        this.$refs.form.setErrors(result.errors)
      }
    },
    getProfileList () {
      this.loading = true
      RestApi.getData(baseURL, 'api/v1/admin/ajax/get_current_profile_list', null).then(response => {
        if (response.success) {
          this.form = response.data
          this.loading = false
        } else {
          this.form = []
        }
      })
    }
  }
}
</script>
<style>
.formBoder {
    border: 1px solid;
    margin: 0px;
    padding: 35px;
    font-size: 13px
 }
 .profile {
    margin: 2px 0px 0px 52px;
    font-size: 21px;
    font-family: auto;
  }
</style>
