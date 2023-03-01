<template>
    <div>
      <div class="flex items-center justify-between mb-3">
        <h1 v-text="title"></h1>
        <div>
          <button class="btn-primary" :disabled="checkJobs || !checkAllDisabled" v-on:click="onTriggerResizeImages(false)">Optimize remaining images</button>
          <button class="btn-primary" :disabled="checkJobs" v-on:click="onTriggerResizeImages(true)">Optimize all images</button>
        </div>
      </div>

      <div v-show="!loadingMessage" class="mt-2">
          <ul class="card p-0 mb-2">
              <li class="flex items-center justify-between py-1 px-2 border-b group">
                <span v-text="unoptimizedAssets"></span> out of <span v-text="totalAssets"></span> assets need to be optimized
              </li>
          </ul>
      </div>

      <div v-show="loadingMessage" class="mt-2">
          <ul class="card p-0 mb-2">
              <li v-text="loadingMessage" class="flex items-center justify-between py-1 px-2 border-b group"></li>
          </ul>
      </div>
    </div>
</template>
<script>
    export default {
      data() {
        return {
          batchId: null,
          jobCount: this.unoptimizedAssets,
          currentJobCount: this.unoptimizedAssets,
          checkJobs: false,
          jobStarted: false,
          resizeUrl: '/cp/statamic-image-optimize/resize-images/',
          resizeAllUrl: '/cp/statamic-image-optimize/resize-images/force-all',
          resizeCheckUrl: '/cp/statamic-image-optimize/resize-images-count/'
        }
      },

      props: {
        title: String,
        buttonText: String,
        totalAssets: Number,
        unoptimizedAssets: Number,
      },

      computed: {
        loadingMessage() {
          if (!this.jobStarted) {
            return '';
          }

          let jobsDone = this.jobCount - this.currentJobCount;
          let jobsLeft = this.jobCount - jobsDone;

          return jobsDone + ' of ' + this.jobCount + ' images have been optimized.';
        },

        checkAllDisabled() {
          return this.unoptimizedAssets > 0;
        }
      },

      methods: {
        async onTriggerResizeImages(forceAll = false) {
          let self = this;
          this.jobStarted = true;
          this.checkJobs = true;
          this.resizeImages(forceAll);

          let resizeInterval = setInterval(async function () {
            let resizeCheckResponse = await self.checkResizeImages();

            if (resizeCheckResponse.assetsToOptimize === undefined || resizeCheckResponse.assetTotal === undefined) {
              this.checkJobs = false;
              clearInterval(resizeInterval);
              return;
            }

            self.jobCount = resizeCheckResponse.assetTotal;
            self.currentJobCount = resizeCheckResponse.assetsToOptimize;

            if (resizeCheckResponse.assetsToOptimize === 0) {
              this.checkJobs = false;
              clearInterval(resizeInterval);
              return;
            }
          }, 1000);
        },

        async resizeImages(forceAll = false) {
          let self = this;

          return await fetch(forceAll ? this.resizeAllUrl : this.resizeUrl)
              .then((response) => response.json())
              .then(function (responseData) {
                self.batchId = responseData.batchId;
                self.checkJobs = false;
                return responseData;
              })
              .catch(function (error) {
                console.error(error);
              });
        },

        async checkResizeImages() {
          return await fetch(this.resizeCheckUrl + this.batchId)
              .then((response) => response.json())
              .then(function (responseData) {
                return responseData;
              })
              .catch(function (error) {
                console.log('error', error);
              });
        }
      }
    }
</script>
