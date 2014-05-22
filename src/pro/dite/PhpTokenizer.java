package pro.dite;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

public class PhpTokenizer
{

    public void tokenize(String content) throws IOException
    {
        Process p = Runtime.getRuntime().exec(new String[] {
            "php",
            "-r",
            "echo json_encode(token_get_all('" + content.replace('\'', '"') + "'));"
        });
        BufferedReader stdInput = new BufferedReader(new InputStreamReader(p.getInputStream()));

        String json = stdInput.readLine();
        System.out.println(json);

    }

}
