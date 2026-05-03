import java.io.*;
import java.net.*;

public class DownloadWebPage {
    public static void main(String[] args) {
        try {
            // Web address to download
            String webAddress = "https://example.com";

            // Create URL object
            URL url = new URL(webAddress);

            // Open stream to read data
            BufferedReader reader = new BufferedReader(new InputStreamReader(url.openStream()));

            // File to save webpage
            BufferedWriter writer = new BufferedWriter(new FileWriter("downloaded_page.html"));

            String line;
            while ((line = reader.readLine()) != null) {
                writer.write(line);
                writer.newLine();
            }

            reader.close();
            writer.close();

            System.out.println("Webpage downloaded successfully!");
        } catch (Exception e) {
            System.out.println("Error: " + e.getMessage());
        }
    }
}