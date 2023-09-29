<template>
  <b-overlay :show="loading">
    <div class="section-wrapper">
      <b-breadcrumb class="custom-bread"></b-breadcrumb>
      <div class="form-wrapper">
        <b-card title="Dashboard">
          <b-card-text>
            <h5 class="mb-2">Driving License Info</h5>
            <b-row>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-primary text-light">
                  <p>Delivered Today</p>
                  <h4>{{ dashboardData.delivered_today }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-primary text-light">
                  <p>Delivered this Month</p>
                  <h4>{{ dashboardData.delivered_this_month }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-primary text-light">
                  <p>Delivered Last 6 Months</p>
                  <h4>{{ dashboardData.delivered_last_6th_month }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-primary text-light">
                  <p>Delivered this Year</p>
                  <h4>{{ dashboardData.delivered_this_year }}</h4>
                </div>
              </b-col>
            </b-row>
            <b-row class="mt-4">
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-primary text-light">
                  <p>Total Delivered</p>
                  <h4>{{ dashboardData.totol_delivered }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-warning text-light">
                  <p>Total Undelivered</p>
                  <h4>{{ dashboardData.totol_un_delivered }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-success text-light">
                  <p>Received Today</p>
                  <h4>{{ dashboardData.received_today }}</h4>
                </div>
              </b-col>
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-info text-light">
                  <p>Entry Today</p>
                  <h4>{{ dashboardData.entry_today }}</h4>
                </div>
              </b-col>
            </b-row>
            <b-row class="mt-4">
              <b-col sm="12" md="3">
                <div class="mc-report-card p-3 bg-secondary text-light">
                  <p>Total Drining License</p>
                  <h4>{{ dashboardData.totol_dl }}</h4>
                </div>
              </b-col>
            </b-row>
          </b-card-text>
        </b-card>
      </div>
        <b-card class="mt-3">
          <b-card-text>
              <!-- <img src="@/assets/images/charts.png" class="img-fluid" alt=""> -->
              <b-row>
                <b-col sm="12" md="12">
                  <div id="chart">
                    <apexchart ref="earningOverviewChart" type="line" height="350" :options="chartOptions" :series="series"></apexchart>
                  </div>
                </b-col>
                <!-- <b-col sm="12" md="6">
                    <div id="chart">
                      <apexchart type="donut" width="380" :options="pieChartOptions" :series="Pieseries"></apexchart>
                    </div>
                </b-col> -->
              </b-row>
          </b-card-text>
        </b-card>
        <!-- <b-card class="mt-3">
          <b-card-text>
              <img src="@/assets/images/charts.png" class="img-fluid" alt="">
              <b-row>
                <b-col sm="12" md="6">
                  <div id="chart">
                    <apexchart type="bar" height="430" :options="barChartOptions" :series="barSeries"></apexchart>
                  </div>
                </b-col>
              </b-row>
          </b-card-text>
        </b-card> -->
    </div>
  </b-overlay>
</template>
<script>
import RestApi, { baseURL } from '@/config'

export default {
  components: {
  },
  data () {
    return {
      dashboardData: '',
      loading: false,
      series: [],
      chartOptions: {
        chart: {
          height: 350,
          type: 'line',
          zoom: {
            enabled: false
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'straight'
        },
        title: {
          // text: 'Last 6 Months Delivery Overview',
          text: 'Current Year Monthly Delivery Overview',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          }
        },
        xaxis: {
          categories: []
        }
      },
      Pieseries: [44, 55],
      pieChartOptions: {
        chart: {
          width: 400,
          type: 'donut'
        },
        plotOptions: {
          pie: {
            startAngle: -90,
            endAngle: 270
          }
        },
        dataLabels: {
          enabled: false
        },
        fill: {
          type: 'gradient'
        },
        // legend: {
        //   formatter: function (val, opts) {
        //     return val + ' - ' + opts.w.globals.series[opts.seriesIndex]
        //   }
        // },
        title: {
          text: 'Installation'
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            }
            // legend: {
            //   position: 'bottom'
            // }
          }
        }]
      },
      barSeries: [
        { data: [44, 55, 41, 64, 22, 43, 21] },
        { data: [53, 32, 33, 52, 13, 44, 32] }
      ],
      barChartOptions: {
        chart: {
          type: 'bar',
          height: 430
        },
        plotOptions: {
          bar: {
            horizontal: true,
            dataLabels: {
              position: 'top'
            }
          }
        },
        dataLabels: {
          enabled: true,
          offsetX: -6,
          style: {
            fontSize: '12px',
            colors: ['#fff']
          }
        },
        stroke: {
          show: true,
          width: 1,
          colors: ['#fff']
        },
        tooltip: {
          shared: true,
          intersect: false
        },
        xaxis: {
          categories: ['chapter-1', 'chapter-2', 'chapter-3', 'chapter-4', 'chapter-5', 'chapter-6', 'chapter-7']
        }
      }
    }
  },
  created () {
    this.getDashboardData()
  },
  mounted () {
  },
  methods: {
    async getDashboardData () {
      this.loading = true
      var result = await RestApi.getData(baseURL, 'api/v1/admin/ajax/get_dashboard_data')
      this.loading = false
      this.dashboardData = result.data
      if (result.data.monthList.length) {
        const monthNameList = result.data.monthList.map(item => {
          return item.month_name
        })
        const monthlyEarningList = result.data.monthList.map(item => {
          return item.earning
        })

        this.$refs.earningOverviewChart.updateOptions({
          xaxis: {
            labels: {
              rotate: -45
            },
            categories: monthNameList,
            tickPlacement: 'on'
          }
        })

        this.$refs.earningOverviewChart.updateSeries([
          {
            name: 'Monthly Delivery',
            data: monthlyEarningList
          }
        ])
      }
      console.log('this.chartOptions.xaxis.categories', this.chartOptions.xaxis.categories)
      // this.chartOptions.xaxis.categories = result.data
    }
  }
}
</script>
<style>
  .mc-report-card{
    border-radius: var(--border-radius-md);
  }
</style>
