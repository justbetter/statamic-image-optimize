<template>
    <div>
      <div class="flex items-center justify-between mb-3">
        <h1 v-text="title"></h1>
        <div v-if="canOptimizeAssets">
          <button class="btn-primary" :disabled="checkJobs || !checkAllDisabled" v-on:click="onTriggerResizeImages(false)">Optimize remaining images</button>
          <button class="btn-primary" :disabled="checkJobs" v-on:click="onTriggerResizeImages(true)">Optimize all images</button>
        </div>
      </div>

      <div v-show="!canOptimizeAssets" class="mt-2">
          <ul class="card p-0 mb-2">
              <li class="flex items-center justify-between py-1 px-2 border-b group">
                You need an active database connection in order to use the optimize addon.
              </li>
          </ul>
      </div>

      <div v-show="!loadingMessage && canOptimizeAssets" class="mt-2">
          <div class="w-full mb-2">
              <div class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <div class="overflow-hidden rounded-lg bg-white shadow">
                      <div class="flex flex-col justify-between items-center w-full h-full p-5">
                            <div class="truncate text-xl text-gray-500">
                                Total amount of images
                            </div>
                            <div v-text="totalAssets" class="text-5xl font-medium text-gray-900"></div>
                      </div>
                  </div>

                  <div class="overflow-hidden rounded-lg bg-white shadow">
                      <div class="flex flex-col justify-between items-center w-full h-full p-5">
                          <div class="truncate text-xl text-gray-500">
                              Images to optimize
                          </div>
                          <div v-text="unoptimizedAssets" class="text-5xl font-medium text-gray-900"></div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <div v-show="loadingMessage && canOptimizeAssets" class="mt-2">
          <div class="w-full mb-2">
              <div class="mt-2 grid grid-cols-1">
                  <div class="overflow-hidden rounded-lg bg-white shadow">
                      <div class="flex flex-col justify-between items-center w-full h-full p-5">
                          <div v-text="loadingMessage" class="truncate text-xl text-gray-500"></div>
                      </div>
                  </div>
              </div>
          </div>
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
          jobsDone: 0,
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
        canOptimize: Number,
      },

      computed: {
        loadingMessage() {
          if (!this.jobStarted) {
            return '';
          }

          this.jobsDone = this.jobCount - this.currentJobCount;

          return this.jobsDone + ' of ' + this.jobCount + ' images have been optimized.';
        },

        checkAllDisabled() {
          return this.unoptimizedAssets > 0;
        },

        canOptimizeAssets() {
          return this.canOptimize >= 1;
        },
      },

      methods: {
        async onTriggerResizeImages(forceAll = false) {
          let self = this;
          this.jobStarted = true;
          this.checkJobs = true;
          await this.resizeImages(forceAll);

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
                console.error(error);
              });
        }
      }
    }
</script>
