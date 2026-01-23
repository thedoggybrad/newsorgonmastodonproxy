export default async function handler(req, res) {
  try {
    // Extract cookies from the incoming request (if any)
    const cookies = req.headers.cookie || "";

    const response = await fetch("https://www.pna.gov.ph/articles/list", {
      method: "GET",
      headers: {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0)",
        "Cookie": cookies,  // Pass cookies from the incoming request
      },
      credentials: "include",  // Ensure cookies are included for cross-origin requests
    });

    const html = await response.text();

    res.setHeader("Content-Type", "text/html");
    res.status(200).send(html);
  } catch (error) {
    res.status(500).send("Failed to fetch content: " + error.message);
  }
}
