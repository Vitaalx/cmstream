import Toanotherback from "../js/libs/taob.min.js";
import { loaderStore } from "./stores/loader.js";

const taob = new Toanotherback({
	prefix: "api",
	indexInfo: "Info",
	parameters: {
		credentials: "include",
	},
	requestInterceptor(request, interParams) {
		if (request.parameters.loader === true) {
			delete request.parameters.loader;
			interParams.closeLoader = loaderStore.push();
		}
		return request;
	},
	responseInterceptor(response, request, interParams) {
		if (interParams.closeLoader !== undefined) {
			interParams.closeLoader();
		}
		return response;
	},
});

export default taob;