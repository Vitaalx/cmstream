import Toanotherback from "https://www.unpkg.com/toanotherback@2.1.5/dist/taob.min.mjs";

const taob = new Toanotherback({
	prefix: "api",
	indexInfo: "Info",
	parameters: {
		credentials: "include",
	}
});

export default taob;