import Toanotherback from "https://www.unpkg.com/toanotherback@2.0.1/src/index.js";

const taob = new Toanotherback({
    prefix: "api",
    parameters: {
		credentials: "include",
	},
});

export default taob;