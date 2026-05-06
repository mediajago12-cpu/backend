import express from "express";
import db from "../config/db.js";

const router = express.Router();

// GET semua kas
router.get("/", async (req, res) => {
  const [rows] = await db.query("SELECT * FROM kas ORDER BY id DESC");
  res.json(rows);
});

// POST tambah kas
router.post("/", async (req, res) => {
  const { bulan, masuk, keluar } = req.body;

  await db.query(
    "INSERT INTO kas (bulan, masuk, keluar) VALUES (?, ?, ?)",
    [bulan, masuk, keluar]
  );

  res.json({ message: "Kas berhasil ditambahkan" });
});

// PUT edit kas
router.put("/:id", async (req, res) => {
  const { id } = req.params;
  const { bulan, masuk, keluar } = req.body;

  await db.query(
    "UPDATE kas SET bulan=?, masuk=?, keluar=? WHERE id=?",
    [bulan, masuk, keluar, id]
  );

  res.json({ message: "Kas berhasil diupdate" });
});

// DELETE kas
router.delete("/:id", async (req, res) => {
  const { id } = req.params;

  await db.query("DELETE FROM kas WHERE id=?", [id]);

  res.json({ message: "Kas berhasil dihapus" });
});

export default router;