import express from "express";
import cors from "cors";

import kasRoutes from "./routes/kas.js";

const app = express();

// middleware
app.use(cors());
app.use(express.json());

// routes
app.use("/api/kas", kasRoutes);

// port wajib pakai ini (Render)
const PORT = process.env.PORT || 3000;

// listen (HANYA 1)
app.listen(PORT, () => {
  console.log("Server jalan di port " + PORT);
});