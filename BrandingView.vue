<template>
    <route-container>
        <div v-loaded.full-screen="isLoaded" class="branding-view">
            <div v-if="localOrganizationData">
                <section>
                    <div class="row m-t-30">
                        <div class="col-md-6 no-padding-left">
                            <div class="col-md-12 section-label-level-2">
                                Font-Family
                            </div>
                            <div class="col-md-12 section-help-text">
                                Your selected font will be the default in Blasts, Content Creator, and in any Automated
                                Newsletters we send.
                            </div>
                            <div class="col-md-12 flex no-padding-left">
                            <span class="col-md-12 section-label-level-3">
                                Default Font
                            </span>
                            <span class="section-label-level-3 col-md-12 m-l-15" v-if="isCustomFont">
                                Display Name
                            </span>
                            </div>
                            <div class="col-lg-12 flex">
                                <span class="col-md-6 no-padding-left">
                                <ui-dropdown-search v-if="selectedFont"
                                        v-bind:items="availableFontList"
                                        v-bind:label="'name'"
                                        v-model="selectedFont"
                                        v-on:change="onChangeFontSelection"
                                        v-pendo-tag="'BrandingCustomFontDropDown'">
                                    <template slot="item" slot-scope="props">
                                        <span v-bind:style="{'font-family': props.item.attributeValue}">{{props.item.name}}</span>
                                    </template>
                                </ui-dropdown-search>
                                    </span>
                                <span v-if="isCustomFont">
                                    <div class="col-md-12 no-padding-left">
                                        <div>
                                            <ui-input class="col-md-12"
                                                      v-model="customFontName" v-pendo-tag="'BrandingCustomFontNameInput'">
                                            </ui-input>
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>

                    </div>
                    <div v-if="isCustomFont" class="no-padding-left">
                        <div class="section-label-level-2 m-t-30 col-md-12 no-padding-left">
                            Custom Font Name
                        </div>
                        <div class="section-help-text col-md-12 no-padding-left">
                            Start with the font you want, then add a common font fallback, and then select either serif or sans-serif from the dropdown menu.
                        </div>
                        <div class="max-width-40 col-md-12 no-padding-left flex">
                            <span class="col-md-10 no-padding-left section-label-level-3">
                            Primary Font
                            <ui-input v-model="customFontPrimary" v-pendo-tag="'BrandingCustomFontPrimaryFont'"></ui-input>
                            </span>
                            <span class="col-md-10 height-32 section-label-level-3">
                            Fallback font
                            <ui-input v-model="customFontFallback" v-pendo-tag="'BrandingCustomFontSecondaryFont'"></ui-input>
                            </span>
                            <span class="m-t-15 col-md-10 no-padding-top-bottom">
                            <ui-dropdown-search v-if="customFontEnd"
                                    v-bind:items="fallbackDropDownOptions"
                                    v-bind:label="'attribute'"
                                    v-model="fontEndObject"
                                    v-pendo-tag="'BrandingCustomFontFallbackDropdown'">
                                <template slot="item" slot-scope="props">
                                    <span>{{props.item.attribute}}</span>
                                </template>
                            </ui-dropdown-search>
                            </span>
                        </div>
                        <div class="m-t-20 m-b-20 col-md-12 no-padding-left">
                            <ui-button
                                    v-bind:type="'primary'"
                                    v-bind:label="'Save as Default'"
                                    v-bind:is-disabled="!isFontAttributeSet"
                                    v-on:click="displayFontConfirmModal"
                                    v-pendo-tag="'BrandingCustomFontSaveButton'">
                            </ui-button>
                        </div>
                    </div>


                    <div class="row m-t-30">
                        <div class="col-md-12 section-label-level-2">
                            Newsletter Header Image
                        </div>

                        <div class="col-md-12">
                            <image-size-information
                                    v-bind:image-size="'600 x 200 pixels'"
                                    v-bind:file-size="'200KB'"
                                    v-bind:file-types="'PNG, JPG, GIF'">
                            </image-size-information>
                        </div>

                        <div class="col-md-12 m-t-20 newsletter-header-image-wrap">
                            <branding-image-picker
                                    v-bind:dimensions="newsletterHeaderDimensions"
                                    v-bind:max-file-size-in-kb="200"
                                    v-bind:value="localOrganizationData.coverImage"
                                    v-on:input="onChangeNewsletterHeader">
                                <template slot="additionalButtons">
                                    <ui-button v-bind:label="'Send Sample'" v-on:click="sendSampleNewsletter"
                                               v-if="canSendSampleNewsletter"
                                               v-pendo-tag="'SendSampleNewsletter'"></ui-button>
                                </template>
                            </branding-image-picker>
                        </div>

                    </div>

                    <div class="header-link-container">

                        <div class="header-link-text" v-if="localOrganizationData.newsletterHeaderUrl === null">
                            Your newsletter image is currently set to link to the <a class="link-style-1"
                                                                                     v-bind:href="contentArchiveLink"
                                                                                     target="_blank">Content Archive</a>
                            page by default.
                        </div>
                        <div class="header-link-text" v-else>
                            Your newsletter image is currently set to link to a custom url: <br/><a class="link-style-1"
                                                                                                    v-bind:href="localOrganizationData.newsletterHeaderUrl"
                                                                                                    target="_blank">{{localOrganizationData.newsletterHeaderUrl}}</a>
                        </div>
                        <div class="header-link-button pull-right">
                            <ui-button v-bind:label="'Edit Link'" v-bind:type="'default'"
                                       v-on:click="isSetHeaderUrlModalOpen=true"
                                       v-pendo-tag="'BrandingEditHeaderLinkButton'"></ui-button>
                        </div>
                    </div>

            <!-- Newsletter Header Image URL Modal -->
            <ui-modal v-model="isSetHeaderUrlModalOpen" v-on:show="onNewsLetterHeaderImgShow"
                      v-on:hidden="onNewsLetterHeaderImgHidden">
                <template slot="header"><span class="header-link-header">Newsletter Header Image Link</span></template>
                <template slot="body">
                    <div class="radio-container">
                        <ui-radio v-on:click="clearNewUrl" v-model="radioSelection" v-bind:radio-value="0"><span
                                class="radio-options">Link to Content Archive</span></ui-radio>
                        <ui-radio v-on:click="setNewUrl" v-model="radioSelection" v-bind:radio-value="1"><span
                                class="radio-options">Link to Custom URL</span></ui-radio>
                    </div>
                    <ui-input class="header-url-input" v-bind:focus-input="focusInput"
                              v-bind:disabled="radioSelection === 0" v-model="newHeaderUrl"></ui-input>
                </template>
                <template slot="footer">
                    <div class="text-right form-group">
                        <ui-button label="Cancel" v-on:click="isSetHeaderUrlModalOpen=false"></ui-button>
                        <ui-button label="Save" v-bind:type="'primary'"
                                   v-on:click="onChangeNewsLetterHeaderUrl(newHeaderUrl)"></ui-button>
                    </div>
                </template>
            </ui-modal>

            <!-- Confirm Custom Font Modal -->
            <ui-modal-confirm v-model="isFontConfirmModalOpen" v-bind:confirm-text="'Confirm'"
                              v-bind:cancel-text="'Cancel'"
                              v-bind:title="'You\'ve selected a custom font to be your default'"
                              v-on:cancel="closeFontConfirmModal" v-on:confirm="setCustomFont">
                <div class="m-b-20">Your selected font will be the default in Blasts, Content Creator, and in any Automated
                    Newsletters we send.
                </div>
                <div class="m-b-20">If your selected custom font in not available on the browser or computer of your
                    audience, we will use your fallback(s).
                </div>
                <div>Not all fonts are available in our system, so the best way to check how your font is working is to
                    send a sample.
                </div>

            </ui-modal-confirm>
        </div>
    </route-container>
</template>


<script lang="ts">
    import {Component, Watch} from "vue-property-decorator";
    import ImageSizeInformation from "@/organization_settings/components/ImageSizeInformation.vue";
    import Organization from "@/shared/domain/Organization";
    import * as _ from 'lodash';
    import {
        ImageCropperDynamicHeightDimension,
        ImageCropperExactDimension
    } from "@/shared/components/ImageCropperDimensions";
    import BrandingImagePicker from "@/organization_settings/components/BrandingImagePicker.vue";
    import BrandingImagePlaceholders from "@/organization_settings/components/BrandingImagePlaceholders.vue";
    import NewsletterSendSampleAction from "@/shared/domain/NewsletterSendSampleAction";
    import IImageCropperDimensions from "@/shared/components/ImageCropperDimensionsInterface";
    import ViewIsLoadedMixin from "@/organization_settings/views/ViewIsLoadedMixin";
    import {mixins} from "vue-class-component";
    import Utilities from "@/shared/Utilities";
    import {AdminPrivilegeFeatures} from "@/shared/services/AdminPrivilegeService";
    import {
        organizationSettingsModule,
        PatchOrganizationActionPayload
    } from "@/stores/modules/OrganizationSettingsModule";
    import CerklAppConstants from "@/shared/CerklAppConstants";
    import UiButton from "@/shared/components/UiButton.vue";
    import {appModule} from "@/stores/modules/AppModule";
    import UiTextArea from "@/shared/components/UiTextArea.vue";
    import CampaignBuilderFontFamily from "@/shared/domain/CampaignBuilderFontFamily";
    import __ from "lodash/fp/__";
    import CustomFontAction from "@/shared/domain/CustomFontAction";
    import AlertMessage from "@/shared/domain/AlertMessage";
    import {ApiService} from "@/shared/services/ApiService";
    import {escapeRegExp} from "tslint/lib/utils";


    declare var swal: any;

    @Component({
        components: {
            BrandingImagePlaceholders,
            BrandingImagePicker,
            ImageSizeInformation,
            UiButton,
            UiTextArea
        }
    })
    export default class BrandingView extends mixins(ViewIsLoadedMixin) {

        radioSelection: number = null;
        newHeaderUrl: string = null;
        isFontConfirmModalOpen: boolean = false;
        availableFontList: CampaignBuilderFontFamily[] = null;
        selectedFont: CampaignBuilderFontFamily = null;
        savedFontId: number = null;
        customFontName: string = '';
        customFontPrimary: string = '';
        customFontFallback: string = '';
        fontEndObject: {attribute: string} = null;
        customFont: CampaignBuilderFontFamily = null;
        fallbackDropDownOptions: {attribute: string}[] = [{attribute:'serif'}, {attribute:'sans-serif'}];


        get isCustomFont() {
            if (this.selectedFont !== null && this.selectedFont !== undefined) {
                return this.selectedFont.organizationId !== null;
            }
        }

        get isFontAttributeSet() {
            let result = false;
            if (this.isValidAttribute(this.customFontPrimary) && this.isValidAttribute(this.customFontFallback) && this.customFontName != null && this.customFontName !== '') {
                result = true;
            }
            return result;
        }

        isValidAttribute(fontAttribute: string){
            if(this.customFontFallback !== null && this.customFontPrimary !== null) {
                return new RegExp('([a-zA-Z0-9])').test(fontAttribute);
            }
        }

        get customFontEnd(){
            if (this.fontEndObject !== undefined && this.fontEndObject !== null){
                return this.fontEndObject;
            }
        }

        set customFontEnd(newValue: {attribute: string}) {
            if (!newValue) {
                return;
            }
            this.fontEndObject = newValue;
        }


        setCustomFont() {
            if (this.isFontAttributeSet) {
                this.isLoaded = false;
                let dataToPost = new CustomFontAction();
                dataToPost.fontAttribute = this.customFontPrimary + ', ' + this.customFontFallback + ', ' + this.customFontEnd.attribute;
                dataToPost.fontName = this.customFontName;

                this.$apiService.getInstance(CustomFontAction).post(dataToPost, null).then(() => {
                    this.isLoaded = true;
                    AlertService.handle_success("Your custom font has been successfully set");
                    this.$router.go(0);
                }).catch(() => {
                    this.isLoaded = true;
                    AlertService.handle_error("We were unable to set your custom font.  Please try again, or contact Customer Success if you have continued issues.");
                });
            }
        }

        clearNewUrl(): void {
            this.newHeaderUrl = null;
        }

        setNewUrl(): void {
            if (this.localOrganizationData.newsletterHeaderUrl !== null) {
                this.newHeaderUrl = this.localOrganizationData.newsletterHeaderUrl;
            }

        }

        onNewsLetterHeaderImgShow(): void {
            this.contentArchiveLinkIframeSrc = '/org/profile.php?id=' + this.localOrganizationData.id;
        }

        onNewsLetterHeaderImgHidden() {
            this.contentArchiveLinkIframeSrc = 'about:blank';
        }

        onChangeFontSelection(newFont: CampaignBuilderFontFamily) {
            if (newFont.organizationId === null && newFont.id !== this.savedFontId) {
                if(newFont.id == null){
                    this.patchOrganization( {
                        isSettingFontToDefault: true,
                        campaignBuilderFontFamilyId: newFont.id
                    });
                }
                else {
                    this.patchOrganization({
                        campaignBuilderFontFamilyId: newFont.id
                    });
                }
                this.savedFontId = newFont.id;
            }
        }

        displayFontConfirmModal() {
            this.isFontConfirmModalOpen = true;
        }

        closeFontConfirmModal() {
            this.isFontConfirmModalOpen = false;
        }


        onChangeNewsletterHeader(newNewsletterHeader: string) {
            this.localOrganizationData.coverImage = newNewsletterHeader;
            this.patchOrganization({
                coverImage: newNewsletterHeader
            });
        }

        onChangeNewsLetterHeaderUrl(newsLetterHeaderUrl: string) {
            if (newsLetterHeaderUrl === null && this.radioSelection === 1) {
                AlertService.handle_error('A URL is required for that selection, please try again.');
                return;
            }
            this.localOrganizationData.newsletterHeaderUrl = newsLetterHeaderUrl;
            this.patchOrganization({
                newsletterHeaderUrl: newsLetterHeaderUrl
            }).then(() => {
                this.isSetHeaderUrlModalOpen = false;
            });
        }


        @Watch('storeOrganizationData', {deep: true, immediate: true})
        onUpdateOrganization(newValue: Organization) {
            if (newValue === null || newValue === undefined) {
                return;
            }


            // Save off the org
            this.localOrganizationData = _.cloneDeep(newValue);
            if (this.localOrganizationData.newsletterHeaderUrl === null) {
                this.radioSelection = 0;
            } else {
                this.radioSelection = 1;
                this.newHeaderUrl = this.localOrganizationData.newsletterHeaderUrl;
            }


            this.$apiService.getInstance(CampaignBuilderFontFamily).getAll(null, null, false).then((responseFonts: CampaignBuilderFontFamily[]) => {
                this.availableFontList = responseFonts;
                let defaultOption = new CampaignBuilderFontFamily();
                defaultOption.attributeValue = null;
                defaultOption.id = null;
                defaultOption.name = 'Default';
                this.availableFontList.unshift(defaultOption);
                if(this.localOrganizationData.campaignBuilderFontFamilyId === null){
                    this.selectedFont = defaultOption;
                }
                else {
                    this.selectedFont = responseFonts.find(curFont => curFont.id === this.localOrganizationData.campaignBuilderFontFamilyId);
                }
                this.savedFontId = this.selectedFont.id;
                let hasCustomFont: boolean = (responseFonts || []).some(curFont => curFont.organizationId !== null && curFont.organizationId !== undefined);
                if (!hasCustomFont) {
                    let customFontOption = new CampaignBuilderFontFamily();
                    customFontOption.id = responseFonts.length + 1;
                    customFontOption.organizationId = CerklAppConstants.OrganizationId;
                    customFontOption.name = '[Custom]';
                    this.availableFontList.push(customFontOption);
                    this.fontEndObject = {attribute: 'serif'};
                } else {
                    this.customFont = responseFonts[responseFonts.length - 1];
                    this.customFontName = this.customFont.name;
                    let strSplit = this.customFont.attributeValue.split(', ');
                    this.customFontPrimary = strSplit[0];
                    this.customFontFallback = strSplit[1];
                    this.fontEndObject = {attribute: strSplit[2]};
                }

            }).catch(() => {
                AlertService.handle_error('Unable to load fonts');
            });

        }
    }
</script>

<style lang="less">
    .branding-view {
        @import '../../../styles/ui/base.less';

        .branded-preview-button {
            display: inline-block;
            position: relative;
            line-height: normal;
            margin-right: .1em;
            cursor: pointer;
            vertical-align: middle;
            text-align: center;
            overflow: visible;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 500;
            color: white;
            border: 2px solid @color-white;
            border-radius: 100px;

            &:hover {
                text-decoration: none;
            }
        }

        .profile-image-preview {
            border-radius: 50%;
        }

        .m-t-20 {
            margin-top: 20px;
        }

        .m-t-15 {
            margin-top: 15px;
        }

        .m-t-10 {
            margin-top: 10px;
        }

        .m-t-30 {
            margin-top: 30px;
        }

        .m-r-20 {
            margin-right: 20px;
        }

        .m-l-15 {
            margin-left: 15px;
        }

        .m-b-20 {
            margin-bottom: 20px;
        }

        .compliance-link {
            color: @color-power-purple;
        }

        .link-preview {
            font-family: "canada-type-gibson", sans-serif !important;
            font-size: 14px;
            font-weight: normal;
            font-style: normal;
            font-stretch: normal;
            line-height: normal;
            letter-spacing: normal;
            color: #333333;
        }

        .content-archive-modal {
            @media (min-width: 768px) {
                .modal-xl {
                    width: 90%;
                    height: 90vh;
                }
            }

            .modal-content {
                height: 100%;
                overflow: hidden;
            }

            /*Sets the maximum height of the modal body to 90% of the screen height*/

            .modal-body {
                height: 100%;
                padding: 0px;
            }

            .head-title {
                font-size: 20px !important;
                line-height: 40px;
            }

            .modal-header {
                span {
                    line-height: 40px;
                    font-size: 34px;
                    font-weight: 200 !important;
                    color: @color-blue-gray;
                    opacity: 1;
                    position: absolute;
                    right: 10px;
                    top: 0;
                    z-index: 100;
                }
            }
        }


        .favicon-preview-container {
            width: 375px;
            position: relative;

            .favicon-image {
                width: 24px;
                height: 24px;
                position: relative;
                bottom: 32px;
                left: 115px;
                background-color: white;
            }
        }

        .header-link-container {
            border-radius: 4px;
            background-color: #f5f7fa;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            margin-top: 16px;
            padding-right: 14px;
            @media (min-width: 769px) {
                width: 30vw;
                min-width: 600px;
                height: auto;
            }
            @media (width: 768px) {
                width: 100%;
                max-width: 600px;
                height: auto;
            }
            @media (max-width: 767px) {
                width: 90vw;
                border-radius: 4px;
            }

        }

        .header-link-text {
            width: 100%;
            height: auto;
            font-size: 15px;
            font-weight: 500;
            font-stretch: normal;
            font-style: normal;
            line-height: 1.43;
            letter-spacing: normal;
            color: #5c5482;
            padding: 16px;
            flex-grow: 1;
            @media (max-width: 767px) {
                width: 100%;
                height: auto;
                font-size: 13px;
            }
        }

        .header-link-button {
            flex-grow: 0;
            white-space: nowrap;
        }

        .link-style-1 {
            color: #9b3d9b;
            word-break: break-all;
        }

        .header-link-modal {
            padding: 8px;
        }

        .header-link-header {
            font-size: 20px;
            font-weight: 600;
            font-stretch: normal;
            font-style: normal;
            line-height: 1.4;
            letter-spacing: normal;
            text-align: center;
            color: #1f1454;
            margin-bottom: -15px;
        }

        .radio-container {
            margin-left: -10px;
        }

        .modal-header {
            padding: 15px 15px 0 18px;
        }

        .header-url-input {
            padding-left: 30px;
        }

        .modal-content {
            padding-left: 5px;
            padding-right: 5px;
        }

        .min-width-40 {
            min-width: 40px;
        }

        .max-width-40 {
            @media (max-width: 768px) {
                width: 90%;
            }
            @media (min-width: 769px) {
                max-width: 40%;
            }
        }
        .max-width-85 {
            width: 85%
        }
        .no-padding-left{
            padding-left: 0 !important;
        }

        .v-select:not(.open) input[type=search] {
            height: 0px;
        }

        .v-select:not(.open) .selected-tag {
            height: auto;
        }

        .v-select.open input[type=search] {
            height: 32px;
        }
        .no-padding-top-bottom{
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
    }


</style>