export default async function handler(req, res) {
  try {
    const response = await fetch("https://www.pna.gov.ph/articles/list", {
      headers: {
        "User-Agent":
          "NetcraftSurveyAgent/1.0",
      },
    });

    const html = await response.text();

    res.setHeader("Content-Type", "text/html");
    res.status(200).send(html);
  } catch (error) {
    res.status(500).send("Failed to fetch content: " + error.message);
  }
}
