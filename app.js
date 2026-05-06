import express from "express";
import cors from "cors";

import kasRoutes from "./routes/kas.js";

const app = express();

app.use(cors());
app.use(express.json());

app.use("/api/kas", kasRoutes);

app.listen(5000, "0.0.0.0", () => {
  console.log("Server jalan di http://10.126.246.6:5000");
});